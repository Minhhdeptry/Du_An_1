<?php
// models/admin/BookingModel.php

require_once "./models/admin/PaymentModel.php";
require_once "./models/admin/BookingItemModel.php";

class BookingModel
{
    private $pdo;
    private $paymentModel;
    private $itemModel;

    public static $statusLabels = [
        'PENDING' => '⏳ Chờ xác nhận',
        'CONFIRMED' => '✅ Đã xác nhận',
        'READY' => '🎯 Sẵn sàng',
        'IN_PROGRESS' => '🚌 Đang diễn ra',
        'COMPLETED' => '🎉 Hoàn tất',
        'CANCELED' => '❌ Đã hủy',
        'REFUNDED' => '💰 Đã hoàn tiền'
    ];

    public static $statusColors = [
        'PENDING' => 'warning',   // Vàng
        'CONFIRMED' => 'info',      // Xanh dương
        'READY' => 'primary',   // Xanh đậm
        'IN_PROGRESS' => 'purple',    // Tím (cần custom CSS)
        'COMPLETED' => 'success',   // Xanh lá
        'CANCELED' => 'danger',    // Đỏ
        'REFUNDED' => 'secondary'  // Xám
    ];

    private static $allowedTransitions = [
        'PENDING' => ['CONFIRMED', 'CANCELED'],
        'CONFIRMED' => ['READY', 'CANCELED'],
        'READY' => ['IN_PROGRESS', 'CANCELED'],
        'IN_PROGRESS' => ['COMPLETED', 'CANCELED'],
        'COMPLETED' => ['REFUNDED'],
        'CANCELED' => ['REFUNDED'],
        'REFUNDED' => []
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->paymentModel = new PaymentModel($pdo);
        $this->itemModel = new BookingItemModel($pdo);
    }

    public function canTransition(string $currentStatus, string $newStatus): bool
    {
        if ($currentStatus === $newStatus) {
            return true; // Không thay đổi
        }

        $allowed = self::$allowedTransitions[$currentStatus] ?? [];
        return in_array($newStatus, $allowed);
    }

    public function validateStatusTransition(string $currentStatus, string $newStatus): array
    {
        if (!$this->canTransition($currentStatus, $newStatus)) {
            $currentLabel = self::$statusLabels[$currentStatus] ?? $currentStatus;
            $newLabel = self::$statusLabels[$newStatus] ?? $newStatus;

            return [
                'ok' => false,
                'errors' => [
                    "❌ Không thể chuyển từ <strong>{$currentLabel}</strong> sang <strong>{$newLabel}</strong>.<br>" .
                        "💡 <strong>Các trạng thái có thể chuyển:</strong> " .
                        implode(', ', array_map(
                            fn($s) => self::$statusLabels[$s] ?? $s,
                            self::$allowedTransitions[$currentStatus] ?? []
                        ))
                ]
            ];
        }

        return ['ok' => true];
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
                WHERE b.status != 'CANCELED' AND b.status != 'REFUNDED'
                ORDER BY b.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchByKeyword($keyword)
    {
        $sql = "SELECT 
                b.*, 
                ts.depart_date, 
                t.title AS tour_name
            FROM bookings b
            LEFT JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
            LEFT JOIN tours t ON t.id = ts.tour_id
            WHERE (b.booking_code LIKE ?
                OR b.contact_name LIKE ?
                OR t.title LIKE ?)
              AND b.status != 'CANCELED'
            ORDER BY b.id DESC";

        $stmt = $this->pdo->prepare($sql);

        $kw = "%{$keyword}%";

        $stmt->execute([$kw, $kw, $kw]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function find($id)
    {
        $sql = "
            SELECT 
                b.*,

                ts.depart_date,
                ts.return_date,

                ts.price_adult     AS price_adult,
                ts.price_children  AS price_children,

                ts.is_custom_request,
                ts.seats_total,
                ts.seats_available,

                t.title AS tour_name,
                t.duration_days

            FROM bookings b
            LEFT JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
            LEFT JOIN tours t ON t.id = ts.tour_id
            WHERE b.id = ?
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($r) {
            // ✅ Tổng người
            $ad = (int) $r['adults'];
            $ch = (int) $r['children'];
            $r['total_people'] = $ad + $ch;

            // ✅ Label trạng thái
            $r['status_label'] = self::$statusLabels[$r['status']] ?? $r['status'];

            // ✅ Fallback an toàn
            $r['price_adult']    = (float) ($r['price_adult'] ?? 0);
            $r['price_children'] = (float) ($r['price_children'] ?? 0);

            // ✅ Tính return_date nếu thiếu
            if (
                empty($r['return_date']) &&
                !empty($r['depart_date']) &&
                !empty($r['duration_days'])
            ) {
                $depart = strtotime($r['depart_date']);
                $return = strtotime('+' . (int)$r['duration_days'] . ' days', $depart);
                $r['return_date'] = date('Y-m-d', $return);
            }
        }

        return $r;
    }

    public function create($data, $author_id = null)
    {
        $errors = $this->validateData($data);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $adults = (int) ($data['adults'] ?? 0);
        $children = (int) ($data['children'] ?? 0);
        $booking_code = $this->generateBookingCode();

        try {
            $this->pdo->beginTransaction();

            $schedule_id = null;
            $isCustomRequest = false;

            if (!empty($data['tour_schedule_id'])) {
                $schedule_id = (int) $data['tour_schedule_id'];
                $isCustomRequest = false;

                $stmt = $this->pdo->prepare("SELECT id FROM tour_schedule WHERE id = ? LIMIT 1");
                $stmt->execute([$schedule_id]);
                if (!$stmt->fetch()) {
                    throw new \Exception("Lịch tour không tồn tại");
                }
            } else {
                $validateSchedule = $this->validateScheduleData($data);
                if ($validateSchedule) {
                    throw new \Exception(implode(', ', $validateSchedule));
                }

                $tour_id = null;
                if (!empty($data['tour_id'])) {
                    $tour_id = (int) $data['tour_id'];
                } elseif (!empty($data['custom_tour_name'])) {
                    $tour_id = $this->createOrGetCustomTour($data['custom_tour_name'], $data);
                    if (!$tour_id) {
                        throw new \Exception("Không thể tạo tour mới");
                    }
                } else {
                    throw new \Exception("Vui lòng chọn tour hoặc nhập tên tour mới");
                }

                $schedule_id = $this->createCustomSchedule($data, $tour_id);
                if (!$schedule_id) {
                    throw new \Exception("Không thể tạo lịch tour");
                }
                $isCustomRequest = true;
            }

            $price_adult = 0;
            $price_children = 0;

            if ($isCustomRequest) {
                // Tour custom: Lấy giá từ form
                $price_adult = (float) ($data['price_adult'] ?? 0);
                $price_children = (float) ($data['price_children'] ?? 0);
            } else {
                // Tour thường: Lấy giá từ schedule
                $pricing = $this->getSchedulePricing($schedule_id);
                $price_adult = $pricing['price_adult'];
                $price_children = $pricing['price_children'];
            }

            // Tính tổng tiền CHƯA BAO GỒM ITEMS (items sẽ thêm sau)
            $total_amount = ($adults * $price_adult) + ($children * $price_children);


            $stmt = $this->pdo->prepare("
                INSERT INTO bookings
                (booking_code, tour_schedule_id, contact_name, contact_phone, contact_email,
                adults, children, total_people, total_amount, 
                status, special_request, user_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', ?, ?, NOW())
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

            $payment_id = null;
            if ($total_amount > 0) {
                $payment_id = $this->paymentModel->createInitialPayment($booking_id, $total_amount);
                if (!$payment_id) {
                    throw new \Exception("Không thể tạo payment tự động");
                }
            }

            // Ghi log
            $logType = $isCustomRequest ? "Tour theo yêu cầu" : "Tour thường";
            $this->pdo->prepare("
                INSERT INTO tour_logs (booking_id, author_id, entry_type, content)
                VALUES (?, ?, 'NOTE', ?)
            ")->execute([
                $booking_id,
                $author_id,
                "Booking được tạo ({$logType}) với trạng thái CHỜ XÁC NHẬN. Payment: PAY-{$payment_id}"
            ]);

            $this->pdo->commit();

            if (!$isCustomRequest) {
                $this->updateSeats($schedule_id);
            }

            return ['ok' => true, 'booking_id' => $booking_id, 'payment_id' => $payment_id];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("BookingModel::create Error: " . $e->getMessage());
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }
    public function recalculateTotal($booking_id)
    {
        $booking = $this->find($booking_id);
        if (!$booking) return false;

        $tour_amount =
            ($booking['adults'] * $booking['price_adult']) +
            ($booking['children'] * $booking['price_children']);

        require_once "./models/admin/BookingItemModel.php";
        $itemModel = new BookingItemModel($this->pdo);
        $items_amount = $itemModel->getItemsTotal($booking_id);

        $total = $tour_amount + $items_amount;

        $this->pdo->prepare("
            UPDATE bookings SET total_amount = ?
            WHERE id = ?
        ")->execute([$total, $booking_id]);

        return $total;
    }

    public function update($id, $data, $author_id = null)
    {
        $old = $this->find($id);
        if (!$old) {
            return ['ok' => false, 'errors' => ['Booking không tồn tại']];
        }

        // Xác định chế độ sửa
        $editMode = $data['edit_mode'] ?? 'LIMITED';

        if ($editMode === 'VIEW_ONLY') {
            return $this->updateContactInfoOnly($id, $data, $old, $author_id);
        }

        // Validate dữ liệu
        $errors = $this->validateData($data);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $adults = (int) ($data['adults'] ?? $old['adults']);
        $children = (int) ($data['children'] ?? $old['children']);
        $schedule_id = (int) ($data['tour_schedule_id'] ?? $old['tour_schedule_id']);
        $status = $data['status'] ?? $old['status'];

        // Validate schedule tồn tại
        if ($schedule_id <= 0) {
            return ['ok' => false, 'errors' => ['Lịch tour không hợp lệ']];
        }

        $stmt = $this->pdo->prepare("SELECT id FROM tour_schedule WHERE id = ? LIMIT 1");
        $stmt->execute([$schedule_id]);
        if (!$stmt->fetch()) {
            return ['ok' => false, 'errors' => ['Lịch tour không tồn tại trong hệ thống']];
        }

        $isCustom = $this->isCustomRequest($schedule_id);

        $reasons = [];

        if ($editMode === 'FULL') {
            $reasons[] = $isCustom ? "Tour theo yêu cầu" : "Chế độ sửa đầy đủ";
        } elseif ($editMode === 'LIMITED') {
            // Check đổi tour
            if ($schedule_id != $old['tour_schedule_id']) {
                $reasons[] = "Đổi sang tour mới (ID: {$schedule_id})";

                if (!$isCustom) {
                    if (!$this->checkCapacity($schedule_id, $adults, $children)) {
                        return [
                            'ok' => false,
                            'errors' => ['❌ Tour mới không đủ chỗ!']
                        ];
                    }
                }
            }

            // Check đổi số người
            if ($adults != $old['adults'] || $children != $old['children']) {
                $reasons[] = "Thay đổi số người: {$old['adults']}NL+{$old['children']}TE → {$adults}NL+{$children}TE";

                if (!$isCustom) {
                    if (!$this->checkCapacity($schedule_id, $adults, $children, $id)) {
                        return [
                            'ok' => false,
                            'errors' => ['❌ Không đủ chỗ trống!']
                        ];
                    }
                }
            }

            // Check đổi giá
            $priceAdultOld = (float) $old['price_adult'];
            $priceAdultNew = (float) ($data['price_adult'] ?? $priceAdultOld);
            if (abs($priceAdultNew - $priceAdultOld) > 0.01) {
                $reasons[] = "Điều chỉnh giá: " . number_format($priceAdultOld) . "đ → " . number_format($priceAdultNew) . "đ";
            }
        }

        if ($status === 'COMPLETED' && $old['status'] !== 'COMPLETED') {
            $paymentStatus = $this->getPaymentStatus($id);
            if ($paymentStatus !== 'FULL_PAID') {
                return [
                    'ok' => false,
                    'errors' => ['❌ Không thể chuyển sang HOÀN TẤT - Chưa thanh toán đủ']
                ];
            }
        }

        $price_adult = (float) ($data['price_adult'] ?? $old['price_adult']);
        $price_children = (float) ($data['price_children'] ?? $old['price_children']);

        $tour_amount = ($adults * $price_adult) + ($children * $price_children);

        require_once "./models/admin/BookingItemModel.php";
        $itemModel = new BookingItemModel($this->pdo);
        $items_amount = $itemModel->getItemsTotal($id);

        $total_amount = $tour_amount + $items_amount;

        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE bookings SET
            tour_schedule_id = ?,
            contact_name = ?,
            contact_phone = ?,
            contact_email = ?,
            adults = ?,
            children = ?,
            total_people = ?,      
            total_amount = ?,          
            status = ?,
            special_request = ?,
            updated_at = NOW()
        WHERE id = ?";

            $this->pdo->prepare($sql)->execute([
                $schedule_id,
                $data['contact_name'] ?? $old['contact_name'],
                $data['contact_phone'] ?? $old['contact_phone'],
                $data['contact_email'] ?? $old['contact_email'],
                $adults,
                $children,
                $adults + $children,
                $total_amount,
                $status,
                $data['special_request'] ?? $old['special_request'],
                $id
            ]);

            // Ghi log thay đổi
            $changes = [];
            $modeLabel = match ($editMode) {
                'FULL' => 'Sửa đầy đủ',
                'LIMITED' => 'Sửa giới hạn',
                'VIEW_ONLY' => 'Chỉ xem',
                default => $editMode
            };
            $changes[] = "Chế độ: {$modeLabel}";

            if (!empty($reasons)) {
                $changes[] = "Lý do: " . implode(", ", $reasons);
            }

            if ($old['tour_schedule_id'] !== $schedule_id) {
                $changes[] = "Đổi lịch tour: {$old['tour_schedule_id']} → {$schedule_id}";
            }

            if ($old['status'] !== $status) {
                $oldLabel = self::$statusLabels[$old['status']] ?? $old['status'];
                $newLabel = self::$statusLabels[$status] ?? $status;
                $changes[] = "Trạng thái: {$oldLabel} → {$newLabel}";
            }

            if ($old['adults'] !== $adults || $old['children'] !== $children) {
                $changes[] = "Số người: {$old['adults']}NL+{$old['children']}TE → {$adults}NL+{$children}TE";
            }

            if (abs($old['total_amount'] - $total_amount) > 0.01) {
                $oldAmount = number_format($old['total_amount']);
                $newAmount = number_format($total_amount);
                $changes[] = "Tổng tiền: {$oldAmount}đ → {$newAmount}đ";
            }

            if (!empty($changes)) {
                $this->pdo->prepare("
                INSERT INTO tour_logs (booking_id, author_id, entry_type, content)
                VALUES (?, ?, 'NOTE', ?)
            ")->execute([
                    $id,
                    $author_id,
                    "Admin cập nhật booking:\n• " . implode("\n• ", $changes)
                ]);
            }

            $this->pdo->commit();

            // Cập nhật seats
            if (!$isCustom) {
                if ($old['tour_schedule_id'] !== $schedule_id) {
                    $this->updateSeats($old['tour_schedule_id']);
                    $this->updateSeats($schedule_id);
                } else {
                    $this->updateSeats($schedule_id);
                }
            }

            if ($isCustom) {
                $this->pdo->prepare("UPDATE tour_schedule SET
                depart_date = ?, return_date = ?, price_adult = ?, price_children = ?
                WHERE id = ?
            ")->execute([
                    $data['depart_date'] ?? $old['depart_date'],
                    $data['return_date'] ?? $old['return_date'],
                    $price_adult,
                    $price_children,
                    $schedule_id
                ]);
            }

            return ['ok' => true, 'message' => '✅ Cập nhật thành công!'];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("BookingModel::update Error: " . $e->getMessage());
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    private function updateContactInfoOnly($id, $data, $old, $author_id = null)
    {
        try {
            $this->pdo->beginTransaction();

            // Chỉ update contact info và special_request
            $sql = "UPDATE bookings SET
            contact_name = ?, 
            contact_phone = ?, 
            contact_email = ?,
            special_request = ?,
            updated_at = NOW()
        WHERE id = ?";

            $this->pdo->prepare($sql)->execute([
                $data['contact_name'] ?? $old['contact_name'],
                $data['contact_phone'] ?? $old['contact_phone'],
                $data['contact_email'] ?? $old['contact_email'],
                $data['special_request'] ?? $old['special_request'],
                $id
            ]);

            // Ghi log
            $changes = [];
            $changes[] = "Chế độ: Chỉ xem (VIEW_ONLY)";

            if ($old['contact_name'] !== ($data['contact_name'] ?? $old['contact_name'])) {
                $changes[] = "Tên khách: {$old['contact_name']} → " . ($data['contact_name'] ?? '');
            }

            if ($old['contact_phone'] !== ($data['contact_phone'] ?? $old['contact_phone'])) {
                $changes[] = "SĐT: {$old['contact_phone']} → " . ($data['contact_phone'] ?? '');
            }

            if ($old['contact_email'] !== ($data['contact_email'] ?? $old['contact_email'])) {
                $changes[] = "Email: {$old['contact_email']} → " . ($data['contact_email'] ?? '');
            }

            if ($old['special_request'] !== ($data['special_request'] ?? $old['special_request'])) {
                $changes[] = "Cập nhật yêu cầu đặc biệt";
            }

            if (count($changes) > 1) { // Có thay đổi ngoài mode
                $this->pdo->prepare("
                INSERT INTO tour_logs (booking_id, author_id, entry_type, content)
                VALUES (?, ?, 'NOTE', ?)
            ")->execute([
                    $id,
                    $author_id,
                    "Admin cập nhật (chế độ hạn chế):\n• " . implode("\n• ", $changes)
                ]);
            }

            $this->pdo->commit();

            return ['ok' => true, 'message' => '✅ Cập nhật thông tin liên hệ thành công!'];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /** ========================
     *  🔍 HELPER: Lấy số chỗ còn trống
     *  ======================== */
    private function getAvailableSeats($schedule_id, $exclude_booking_id = null): int
    {
        $stmt = $this->pdo->prepare("SELECT seats_total FROM tour_schedule WHERE id = ?");
        $stmt->execute([$schedule_id]);
        $sc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sc) {
            return 0;
        }

        $seats_total = (int) $sc['seats_total'];

        if ($seats_total <= 0) {
            return PHP_INT_MAX; // Không giới hạn
        }

        // Tính số chỗ đã book
        $sql = "SELECT SUM(adults + children) AS booked
        FROM bookings
        WHERE tour_schedule_id = ? 
        AND status IN ('PENDING','CONFIRMED','READY','IN_PROGRESS','COMPLETED')";

        $params = [$schedule_id];

        if ($exclude_booking_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_booking_id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $booked = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['booked'] ?? 0);

        return max(0, $seats_total - $booked);
    }

    /** ========================
     *  Helper: Lấy giá từ schedule
     *  ======================== */
    private function getSchedulePricing($schedule_id): array
    {
        $stmt = $this->pdo->prepare("
        SELECT price_adult, price_children 
        FROM tour_schedule 
        WHERE id = ?
    ");
        $stmt->execute([$schedule_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'price_adult' => (float) ($result['price_adult'] ?? 0),
            'price_children' => (float) ($result['price_children'] ?? 0)
        ];
    }

    /** ========================
     *  ✅ HỦY BOOKING
     *  ======================== */
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
                "Booking đã bị HỦY"
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

    /** ========================
     *  ✅ XÁC NHẬN BOOKING (Chỉ ghi log, không đổi status)
     *  Status sẽ tự động đổi khi có payment
     *  ======================== */

    public function confirmBooking($booking_id, $author_id = null)
    {
        $b = $this->find($booking_id);
        if (!$b) {
            return ['ok' => false, 'errors' => ['Booking không tồn tại']];
        }

        if ($b['status'] !== 'PENDING') {
            return ['ok' => false, 'errors' => ['Chỉ có thể xác nhận booking đang ở trạng thái Chờ xác nhận']];
        }

        try {

            $this->pdo->beginTransaction();

            // ✅ Chuyển sang CONFIRMED (không tự động sang READY nữa)
            $newStatus = 'CONFIRMED';
            $logMessage = "Admin đã XÁC NHẬN booking. Chờ khách thanh toán.";

            // ✅ Update status
            $this->pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?")
                ->execute([$newStatus, $booking_id]);

            // Ghi log
            $this->pdo->prepare("
                INSERT INTO tour_logs (booking_id, author_id, entry_type, content)
                VALUES (?, ?, 'NOTE', ?)
            ")->execute([$booking_id, $author_id, $logMessage]);

            $this->pdo->commit();

            return ['ok' => true, 'message' => $logMessage];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    public function markAsReady($booking_id, $author_id = null)
    {
        $b = $this->find($booking_id);
        if (!$b) {
            return ['ok' => false, 'errors' => ['Booking không tồn tại']];
        }

        $validation = $this->validateStatusTransition($b['status'], 'READY');
        if (!$validation['ok']) {
            return $validation;
        }

        $paymentStatus = $this->getPaymentStatus($booking_id);
        if (!in_array($paymentStatus, ['DEPOSIT_PAID', 'FULL_PAID'])) {
            return [
                'ok' => false,
                'errors' => ['❌ Phải thanh toán (cọc hoặc đủ) trước khi chuyển sang Sẵn sàng']
            ];
        }

        try {
            $this->pdo->prepare("UPDATE bookings SET status = 'READY' WHERE id = ?")
                ->execute([$booking_id]);

            $this->pdo->prepare("
                INSERT INTO tour_logs (booking_id, author_id, entry_type, content)
                VALUES (?, ?, 'NOTE', ?)
            ")->execute([
                $booking_id,
                $author_id,
                "Booking chuyển sang SẴN SÀNG. Đã thanh toán: {$paymentStatus}"
            ]);


            return ['ok' => true, 'message' => 'Đã chuyển sang Sẵn sàng'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    public function startTour($booking_id, $author_id = null)
    {
        $b = $this->find($booking_id);
        if (!$b) {
            return ['ok' => false, 'errors' => ['Booking không tồn tại']];
        }

        $validation = $this->validateStatusTransition($b['status'], 'IN_PROGRESS');
        if (!$validation['ok']) {
            return $validation;
        }

        try {
            $this->pdo->prepare("UPDATE bookings SET status = 'IN_PROGRESS' WHERE id = ?")
                ->execute([$booking_id]);

            $this->pdo->prepare("
                INSERT INTO tour_logs (booking_id, author_id, entry_type, content)
                VALUES (?, ?, 'NOTE', ?)
            ")->execute([
                $booking_id,
                $author_id,
                "Tour đã BẮT ĐẦU"
            ]);

            return ['ok' => true, 'message' => 'Tour đã bắt đầu'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }
    public function markAsCompleted($booking_id, $author_id = null)
    {
        $b = $this->find($booking_id);
        if (!$b) {
            return ['ok' => false, 'errors' => ['Booking không tồn tại']];
        }

        $validation = $this->validateStatusTransition($b['status'], 'COMPLETED');
        if (!$validation['ok']) {
            return $validation;
        }

        $paymentStatus = $this->getPaymentStatus($booking_id);
        if ($paymentStatus !== 'FULL_PAID') {
            return [
                'ok' => false,
                'errors' => ['❌ Phải thanh toán đủ trước khi hoàn tất booking']
            ];
        }

        try {
            $this->pdo->prepare("UPDATE bookings SET status = 'COMPLETED' WHERE id = ?")
                ->execute([$booking_id]);

            $this->pdo->prepare("
                INSERT INTO tour_logs (booking_id, author_id, entry_type, content)
                VALUES (?, ?, 'NOTE', ?)
            ")->execute([
                $booking_id,
                $author_id,
                "Tour đã HOÀN TẤT"
            ]);

            return ['ok' => true, 'message' => 'Tour đã hoàn tất'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    public function refund($booking_id, $author_id = null, $refundAmount = null, $reason = '')
    {
        $b = $this->find($booking_id);
        if (!$b) {
            return ['ok' => false, 'errors' => ['Booking không tồn tại']];
        }

        $validation = $this->validateStatusTransition($b['status'], 'REFUNDED');
        if (!$validation['ok']) {
            return $validation;
        }

        try {
            $this->pdo->beginTransaction();

            $this->pdo->prepare("UPDATE bookings SET status = 'REFUNDED' WHERE id = ?")
                ->execute([$booking_id]);

            if ($refundAmount && $refundAmount > 0) {
                $this->paymentModel->createRefundPayment($booking_id, $refundAmount, $reason);
            }

            // Ghi log
            $logContent = "Đã HOÀN TIỀN cho booking";
            if ($refundAmount) {
                $logContent .= " - Số tiền: " . number_format($refundAmount) . " VNĐ";
            }
            if ($reason) {
                $logContent .= " - Lý do: {$reason}";
            }

            $this->pdo->prepare("
                INSERT INTO tour_logs (booking_id, author_id, entry_type, content)
                VALUES (?, ?, 'NOTE', ?)
            ")->execute([$booking_id, $author_id, $logContent]);

            $this->pdo->commit();

            return ['ok' => true, 'message' => 'Đã hoàn tiền thành công'];
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
            return true; // Tour custom = không giới hạn chỗ
        }

        $stmt = $this->pdo->prepare("SELECT seats_total FROM tour_schedule WHERE id = ?");
        $stmt->execute([$schedule_id]);
        $sc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sc) {
            return false; // Schedule không tồn tại
        }

        $seats_total = (int) $sc['seats_total'];

        if ($seats_total <= 0) {
            return true;
        }

        $sql = "SELECT SUM(adults + children) AS booked
            FROM bookings
            WHERE tour_schedule_id = ? 
            AND status IN ('PENDING','CONFIRMED','DEPOSIT_PAID','COMPLETED')";

        $params = [$schedule_id];

        if ($booking_id) {
            $sql .= " AND id != ?";
            $params[] = $booking_id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $booked = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['booked'] ?? 0);

        $total_after = $booked + $adults + $children;

        return $total_after <= $seats_total;
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
            AND status IN ('PENDING','DEPOSIT_PAID','COMPLETED')
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
        try {
            $stmt = $this->pdo->prepare("
            SELECT is_custom_request 
            FROM tour_schedule 
            WHERE id = ? 
            LIMIT 1
        ");
            $stmt->execute([$schedule_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return false; // Schedule không tồn tại
            }

            return (int) $row['is_custom_request'] === 1;
        } catch (\Throwable $e) {
            error_log("isCustomRequest Error: " . $e->getMessage());
            return false;
        }
    }

    public function getPaymentStatus($booking_id)
    {
        return $this->paymentModel->getPaymentStatus($booking_id);
    }

    public function getTotalPaid($booking_id)
    {
        return $this->paymentModel->getTotalPaid($booking_id);
    }

    private function generateBookingCode(): string
    {
        return 'BK' . date('ymd') . rand(1000, 9999);
    }

    private function createOrGetCustomTour(string $tourName, array $data): ?int
    {
        $tourName = trim($tourName);
        $normalizedName = $this->normalizeString($tourName);

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

        $categoryStmt = $this->pdo->prepare("
            SELECT id FROM tour_category 
            WHERE code = 'REQ' OR name LIKE '%theo yêu cầu%'
            LIMIT 1
        ");
        $categoryStmt->execute();
        $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);
        $customCategoryId = $category['id'] ?? null;

        $code = 'CUSTOM-' . date('ymd') . rand(100, 999);
        $duration = !empty($data['return_date']) && !empty($data['depart_date'])
            ? (strtotime($data['return_date']) - strtotime($data['depart_date'])) / 86400
            : 1;

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
            $customCategoryId
        ]);

        return (int) $this->pdo->lastInsertId();
    }

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

    private function normalizeString(string $str): string
    {
        $str = mb_strtolower($str, 'UTF-8');
        $str = preg_replace('/[^a-z0-9]/', '', $str);
        return $str;
    }

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

    public function validateScheduleData(array $data): array
    {
        $errors = [];

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

    // models/admin/BookingModel.php

    public function getOpenSchedules(): array
    {
        $sql = "SELECT 
               ts.id, 
               ts.depart_date, 
               ts.return_date,
               ts.seats_available, 
               ts.seats_total,
               ts.price_adult, 
               ts.price_children,
               ts.is_custom_request,
               ts.status,
               t.title AS tour_title, 
               t.duration_days, 
               c.name AS category_name,
               CASE 
                   WHEN ts.depart_date < CURDATE() THEN 1
                   ELSE 0
               END AS is_past_date
            FROM tour_schedule ts
            JOIN tours t ON t.id = ts.tour_id
            LEFT JOIN tour_category c ON c.id = t.category_id
            WHERE ts.status = 'OPEN'
            ORDER BY 
              is_past_date ASC,           
              ts.is_custom_request ASC, 
              ts.depart_date ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
