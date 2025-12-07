<?php
class BookingModel
{
    private $pdo;

    public static $statusLabels = [
        'PENDING' => 'Chờ xác nhận',
        'CONFIRMED' => 'Đã xác nhận',
        'PAID' => 'Đã thanh toán',
        'COMPLETED' => 'Hoàn tất',
        'CANCELED' => 'Đã hủy',
    ];

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function getAll()
    {
        $sql = "SELECT b.*, ts.depart_date, t.title AS tour_name
                FROM bookings b
                JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
                JOIN tours t ON t.id = ts.tour_id
                WHERE b.status != 'CANCELED'
                ORDER BY b.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchByKeyword($keyword)
    {
        $sql = "SELECT b.*, ts.depart_date, t.title AS tour_name
                FROM bookings b
                LEFT JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
                LEFT JOIN tours t ON t.id = ts.tour_id
                WHERE (b.booking_code LIKE :kw OR b.contact_name LIKE :kw OR t.title LIKE :kw)
                  AND b.status != 'CANCELED'
                ORDER BY b.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':kw' => "%$keyword%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $sql = "SELECT b.*, ts.depart_date, t.title AS tour_name,
                       ts.seats_total, ts.seats_available, ts.price_adult, ts.price_children,
                       ts.is_custom_request
                FROM bookings b
                LEFT JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
                LEFT JOIN tours t ON t.id = ts.tour_id
                WHERE b.id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($r) {
            $ad = (int) $r['adults'];
            $ch = (int) $r['children'];
            $r['total_people'] = $ad + $ch;
            $r['status_label'] = self::$statusLabels[$r['status']] ?? $r['status'];
        }

        return $r;
    }

    /** ------------------------
     *  ✅ Tạo booking mới
     */
    public function create($data, $author_id = null)
    {
        $errors = $this->validateData($data);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $scheduleErrors = $this->validateScheduleData($data);
        if ($scheduleErrors) {
            return ['ok' => false, 'errors' => $scheduleErrors];
        }

        $adults = (int) ($data['adults'] ?? 0);
        $children = (int) ($data['children'] ?? 0);
        $booking_code = $this->generateBookingCode();

        try {
            $this->pdo->beginTransaction();

            // ✅ Xử lý tour_id
            $tour_id = null;

            if (!empty($data['tour_id'])) {
                // Mode 1: Chọn tour có sẵn
                $tour_id = (int) $data['tour_id'];
            } elseif (!empty($data['custom_tour_name'])) {
                // Mode 2: Tạo tour mới
                $tour_id = $this->createOrGetCustomTour($data['custom_tour_name'], $data);
                if (!$tour_id) {
                    throw new \Exception("Không thể tạo tour mới");
                }
            } else {
                throw new \Exception("Vui lòng chọn tour hoặc nhập tên tour mới");
            }

            // Tạo schedule
            $schedule_id = $this->createCustomSchedule($data, $tour_id);
            if (!$schedule_id) {
                throw new \Exception("Không thể tạo lịch tour");
            }

            // Tính tổng tiền
            $price_adult = (float) ($data['price_adult'] ?? 0);
            $price_children = (float) ($data['price_children'] ?? 0);
            $total_amount = ($adults * $price_adult) + ($children * $price_children);

            // Tạo booking
            $stmt = $this->pdo->prepare("
                INSERT INTO bookings
                (booking_code, tour_schedule_id, contact_name, contact_phone, contact_email,
                 adults, children, total_people, total_amount, status, special_request, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', ?, ?)
            ");

            $stmt->execute([
                $booking_code,
                $schedule_id,
                $data['contact_name'] ?? '',
                $data['contact_phone'] ?? '',
                $data['contact_email'] ?? '',
                $adults,
                $children,
                $adults + $children,
                $total_amount,
                $data['special_request'] ?? '',
                $author_id
            ]);

            $booking_id = $this->pdo->lastInsertId();

            // Ghi log
            $this->pdo->prepare("
                INSERT INTO tour_logs (booking_id, author_id, entry_type, content)
                VALUES (?, ?, 'NOTE', ?)
            ")->execute([
                        $booking_id,
                        $author_id,
                        "Booking created with " . (!empty($data['tour_id']) ? "existing tour" : "custom tour: " . $data['custom_tour_name'])
                    ]);

            $this->pdo->commit();

            return ['ok' => true, 'booking_id' => $booking_id];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /** ------------------------
     *  ✅ Tạo hoặc lấy tour custom
     */
    private function createOrGetCustomTour(string $tourName, array $data): ?int
    {
        $tourName = trim($tourName);
        $normalizedName = $this->normalizeString($tourName);

        // Tìm tour tương tự
        $stmt = $this->pdo->prepare("
        SELECT id FROM tours 
        WHERE LOWER(REPLACE(REPLACE(REPLACE(title, ' ', ''), '-', ''), '_', '')) = ?
        LIMIT 1
    ");
        $stmt->execute([$normalizedName]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return (int) $existing['id'];
        }

        // ✅ MỚI: Lấy ID của category "Tour theo yêu cầu"
        $categoryStmt = $this->pdo->prepare("
        SELECT id FROM tour_category 
        WHERE code = 'REQ' OR name LIKE '%theo yêu cầu%'
        LIMIT 1
        ");
        $categoryStmt->execute();
        $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);
        $customCategoryId = $category['id'] ?? null;

        // Tạo mới
        $code = 'CUSTOM-' . date('ymd') . rand(100, 999);
        $duration = !empty($data['return_date']) && !empty($data['depart_date'])
            ? (strtotime($data['return_date']) - strtotime($data['depart_date'])) / 86400
            : 1;

        // ✅ MỚI: Thêm category_id vào câu INSERT
        $stmt = $this->pdo->prepare("
        INSERT INTO tours 
        (code, title, short_desc, duration_days, adult_price, child_price, category_id, is_active, is_custom)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1)
    ");

        $stmt->execute([
            $code,
            $tourName,
            "Tour theo yêu cầu khách hàng",
            (int) $duration,
            (float) ($data['price_adult'] ?? 0),
            (float) ($data['price_children'] ?? 0),
            $customCategoryId  // ✅ MỚI: Gán category
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** ------------------------
     *  Helper: Chuẩn hóa string
     */
    private function normalizeString(string $str): string
    {
        $str = mb_strtolower($str, 'UTF-8');
        $str = preg_replace('/[^a-z0-9]/', '', $str);
        return $str;
    }

    /** ------------------------
     *  ✅ Tạo schedule custom (FIXED - chỉ 1 version)
     */
    private function createCustomSchedule(array $data, int $tour_id): ?int
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO tour_schedule
                (tour_id, depart_date, return_date, seats_total, seats_available,
                 price_adult, price_children, status, is_custom_request, note)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'OPEN', 1, ?)
            ");

            $total_people = (int) ($data['adults'] ?? 0) + (int) ($data['children'] ?? 0);

            $stmt->execute([
                $tour_id,
                $data['depart_date'] ?? null,
                $data['return_date'] ?? null,
                $total_people,
                $total_people,
                (float) ($data['price_adult'] ?? 0),
                (float) ($data['price_children'] ?? 0),
                'Custom request for: ' . ($data['contact_name'] ?? '')
            ]);

            return (int) $this->pdo->lastInsertId();

        } catch (\Throwable $e) {
            error_log("CreateCustomSchedule Error: " . $e->getMessage());
            return null;
        }
    }

    /** ------------------------
     *  ✅ Mã booking ngắn: BK + YMD + 4 số random
     */
    private function generateBookingCode(): string
    {
        return 'BK' . date('ymd') . rand(1000, 9999); // VD: BK2412065432
    }

    /** ------------------------
     *  ✅ Validate booking data
     */
    public function validateData(array $data): array
    {
        $errors = [];

        if (empty(trim($data['contact_name'] ?? ''))) {
            $errors[] = "Tên khách không được để trống.";
        }

        $adults = (int) ($data['adults'] ?? 0);
        $children = (int) ($data['children'] ?? 0);
        if ($adults + $children <= 0) {
            $errors[] = "Số lượng khách phải lớn hơn 0.";
        }

        if (!empty($data['contact_email']) && !filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email không hợp lệ.";
        }

        if (!empty($data['contact_phone']) && !preg_match('/^[0-9\+\-\s()]{7,15}$/', $data['contact_phone'])) {
            $errors[] = "Số điện thoại không hợp lệ (7-15 ký tự).";
        }

        return $errors;
    }

    /** ------------------------
     *  ✅ Validate schedule data (FIXED - chỉ 1 version)
     */
    public function validateScheduleData(array $data): array
    {
        $errors = [];

        // Phải có tour_id HOẶC custom_tour_name
        if (empty($data['tour_id']) && empty(trim($data['custom_tour_name'] ?? ''))) {
            $errors[] = "Vui lòng chọn tour có sẵn hoặc nhập tên tour mới.";
        }

        if (empty($data['depart_date'])) {
            $errors[] = "Ngày khởi hành không được để trống.";
        } else {
            $departDate = strtotime($data['depart_date']);
            if ($departDate < strtotime('today')) {
                $errors[] = "Ngày khởi hành phải từ hôm nay trở đi.";
            }
        }

        if (!empty($data['return_date']) && !empty($data['depart_date'])) {
            if (strtotime($data['return_date']) < strtotime($data['depart_date'])) {
                $errors[] = "Ngày về phải sau ngày khởi hành.";
            }
        }

        $priceAdult = (float) ($data['price_adult'] ?? 0);
        if ($priceAdult <= 0) {
            $errors[] = "Giá người lớn phải lớn hơn 0.";
        }

        $priceChildren = (float) ($data['price_children'] ?? 0);
        if ($priceChildren < 0) {
            $errors[] = "Giá trẻ em không được âm.";
        }

        return $errors;
    }

    /** ------------------------
     *  Cập nhật booking
     */
    public function update($id, $data, $author_id = null)
    {
        $old = $this->find($id);
        if (!$old) {
            return ['ok' => false, 'errors' => ['Booking không tồn tại']];
        }

        $errors = $this->validateData($data);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $adults = (int) ($data['adults'] ?? $old['adults']);
        $children = (int) ($data['children'] ?? $old['children']);
        $schedule_id = (int) ($data['tour_schedule_id'] ?? $old['tour_schedule_id']);
        $status = $data['status'] ?? $old['status'];

        if (!$this->isCustomRequest($schedule_id)) {
            return ['ok' => false, 'errors' => ['Admin chỉ được cập nhật booking cho tour theo yêu cầu']];
        }

        if (!$this->checkCapacity($schedule_id, $adults, $children, $id)) {
            return ['ok' => false, 'errors' => ['Không đủ chỗ để cập nhật!']];
        }

        $total_amount = $this->calculateTotal($schedule_id, $adults, $children);

        try {
            $this->pdo->beginTransaction();

            $this->pdo->prepare("
                UPDATE bookings SET
                    tour_schedule_id = ?, contact_name = ?, contact_phone = ?, contact_email = ?,
                    adults = ?, children = ?, total_people = ?, total_amount = ?, status = ?, special_request = ?
                WHERE id = ?
            ")->execute([
                        $schedule_id,
                        $data['contact_name'] ?? '',
                        $data['contact_phone'] ?? '',
                        $data['contact_email'] ?? '',
                        $adults,
                        $children,
                        $adults + $children,
                        $total_amount,
                        $status,
                        $data['special_request'] ?? '',
                        $id
                    ]);

            if ($old['status'] !== $status) {
                $this->pdo->prepare("
                    INSERT INTO tour_logs (booking_id, author_id, entry_type, content)
                    VALUES (?, ?, 'NOTE', ?)
                ")->execute([
                            $id,
                            $author_id,
                            "Status changed from " . (self::$statusLabels[$old['status']] ?? $old['status']) .
                            " to " . (self::$statusLabels[$status] ?? $status)
                        ]);
            }

            $this->pdo->commit();

            $this->updateSeats($old['tour_schedule_id']);
            if ($old['tour_schedule_id'] !== $schedule_id) {
                $this->updateSeats($schedule_id);
            }

            return ['ok' => true];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    public function cancelBooking($id, $author_id = null)
    {
        $b = $this->find($id);
        if (!$b) {
            return ['ok' => false, 'errors' => ['Booking không tồn tại']];
        }
        if ($b['status'] === 'CANCELED') {
            return ['ok' => false, 'errors' => ['Booking đã bị hủy trước đó']];
        }

        try {
            $this->pdo->beginTransaction();

            $this->pdo->prepare("UPDATE bookings SET status = 'CANCELED' WHERE id = ?")
                ->execute([$id]);

            $this->pdo->prepare("
                INSERT INTO tour_logs (booking_id, author_id, entry_type, content)
                VALUES (?, ?, 'NOTE', ?)
            ")->execute([
                        $id,
                        $author_id,
                        "Status changed from " . (self::$statusLabels[$b['status']] ?? $b['status']) .
                        " to " . self::$statusLabels['CANCELED']
                    ]);

            $this->pdo->commit();
            $this->updateSeats($b['tour_schedule_id']);

            return ['ok' => true];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    public function confirmBooking($booking_id, $author_id = null)
    {
        $b = $this->find($booking_id);
        if (!$b) {
            return ['ok' => false, 'errors' => ['Booking không tồn tại']];
        }
        if ($b['status'] !== 'PENDING') {
            return ['ok' => false, 'errors' => ['Booking không ở trạng thái chờ xác nhận']];
        }

        try {
            $this->pdo->beginTransaction();

            $this->pdo->prepare("UPDATE bookings SET status = 'CONFIRMED' WHERE id = ?")
                ->execute([$booking_id]);

            $this->pdo->prepare("
                INSERT INTO tour_logs (booking_id, author_id, entry_type, content)
                VALUES (?, ?, 'NOTE', ?)
            ")->execute([
                        $booking_id,
                        $author_id,
                        "Status changed from " . self::$statusLabels['PENDING'] .
                        " to " . self::$statusLabels['CONFIRMED']
                    ]);

            $this->pdo->commit();

            if (!$this->isCustomRequest($b['tour_schedule_id'])) {
                $this->updateSeats($b['tour_schedule_id']);
            }

            return ['ok' => true];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    public function getStatusHistory($booking_id)
    {
        $sql = "SELECT l.content, l.created_at, u.full_name AS author_name
                FROM tour_logs l
                LEFT JOIN users u ON u.id = l.author_id
                WHERE l.booking_id = ?
                ORDER BY l.created_at ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$booking_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calculateTotal($schedule_id, $adults, $children)
    {
        $stmt = $this->pdo->prepare("SELECT price_adult, price_children FROM tour_schedule WHERE id = ?");
        $stmt->execute([$schedule_id]);
        $sc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sc) {
            return 0;
        }

        return ($adults * (float) $sc['price_adult']) + ($children * (float) $sc['price_children']);
    }

    public function checkCapacity($schedule_id, $adults, $children, $booking_id = null)
    {
        if ($this->isCustomRequest($schedule_id)) {
            return true;
        }

        $stmt = $this->pdo->prepare("SELECT seats_total FROM tour_schedule WHERE id = ?");
        $stmt->execute([$schedule_id]);
        $sc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sc) {
            return false;
        }

        $sql = "SELECT SUM(adults + children) AS booked
                FROM bookings
                WHERE tour_schedule_id = ? 
                AND status IN ('PENDING','CONFIRMED','PAID','COMPLETED')";
        $params = [$schedule_id];

        if ($booking_id) {
            $sql .= " AND id != ?";
            $params[] = $booking_id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $booked = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['booked'] ?? 0);

        return ($booked + $adults + $children) <= (int) $sc['seats_total'];
    }

    public function updateSeats($schedule_id)
    {
        if ($this->isCustomRequest($schedule_id)) {
            return;
        }

        $stmt = $this->pdo->prepare("
            SELECT SUM(adults + children) AS booked
            FROM bookings
            WHERE tour_schedule_id = ? 
            AND status IN ('PENDING','CONFIRMED','PAID','COMPLETED')
        ");
        $stmt->execute([$schedule_id]);
        $booked = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['booked'] ?? 0);

        $stmt = $this->pdo->prepare("
            UPDATE tour_schedule
            SET seats_available = seats_total - ?
            WHERE id = ?
        ");
        $stmt->execute([$booked, $schedule_id]);
    }

    public function getSchedules()
    {
        $sql = "SELECT ts.id, ts.depart_date, ts.seats_available, ts.price_adult, ts.price_children,
                       t.title AS tour_title, t.code AS tour_code
                FROM tour_schedule ts
                JOIN tours t ON t.id = ts.tour_id
                WHERE ts.status = 'OPEN'
                  AND ts.is_custom_request = 1
                ORDER BY ts.depart_date ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isCustomRequest($schedule_id)
    {
        $stmt = $this->pdo->prepare("SELECT is_custom_request FROM tour_schedule WHERE id = ?");
        $stmt->execute([$schedule_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($row['is_custom_request']);
    }
}