<?php
// models/admin/BookingModel.php

require_once "./models/admin/PaymentModel.php";

class BookingModel
{
    private $pdo;
    private $paymentModel;

    // ✅ CẬP NHẬT: 7 trạng thái mới
    public static $statusLabels = [
        'PENDING' => '⏳ Chờ xác nhận',
        'CONFIRMED' => '✅ Đã xác nhận',
        'READY' => '🎯 Sẵn sàng',
        'IN_PROGRESS' => '🚌 Đang diễn ra',
        'COMPLETED' => '🎉 Hoàn tất',
        'CANCELED' => '❌ Đã hủy',
        'REFUNDED' => '💰 Đã hoàn tiền'
    ];

    // ✅ THÊM: Badge colors cho UI
    public static $statusColors = [
        'PENDING' => 'warning',   // Vàng
        'CONFIRMED' => 'info',      // Xanh dương
        'READY' => 'primary',   // Xanh đậm
        'IN_PROGRESS' => 'purple',    // Tím (cần custom CSS)
        'COMPLETED' => 'success',   // Xanh lá
        'CANCELED' => 'danger',    // Đỏ
        'REFUNDED' => 'secondary'  // Xám
    ];

    // ✅ THÊM: Quy tắc chuyển trạng thái
    private static $allowedTransitions = [
        'PENDING' => ['CONFIRMED', 'CANCELED'],
        'CONFIRMED' => ['READY', 'CANCELED'],
        'READY' => ['IN_PROGRESS', 'CANCELED'],
        'IN_PROGRESS' => ['COMPLETED', 'CANCELED'],
        'COMPLETED' => ['REFUNDED'],
        'CANCELED' => ['REFUNDED'],
        'REFUNDED' => []
    ];

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
        $this->paymentModel = new PaymentModel($this->pdo);
    }

    // ✅ THÊM: Kiểm tra có thể chuyển trạng thái không
    public function canTransition(string $currentStatus, string $newStatus): bool
    {
        if ($currentStatus === $newStatus) {
            return true; // Không thay đổi
        }

        $allowed = self::$allowedTransitions[$currentStatus] ?? [];
        return in_array($newStatus, $allowed);
    }

    // ✅ THÊM: Validate status transition với message rõ ràng
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
        $sql = "SELECT b.*, 
               ts.depart_date, 
               ts.return_date,
               ts.price_adult as schedule_price_adult, 
               ts.price_children as schedule_price_children,
               ts.is_custom_request,  -- ✅ THÊM field này
               t.title AS tour_name,
               t.duration_days,
               ts.seats_total, 
               ts.seats_available
        FROM bookings b
        LEFT JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
        LEFT JOIN tours t ON t.id = ts.tour_id
        WHERE b.id = ? 
        LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($r) {
            // ✅ Tính tổng người
            $ad = (int) $r['adults'];
            $ch = (int) $r['children'];
            $r['total_people'] = $ad + $ch;
            $r['status_label'] = self::$statusLabels[$r['status']] ?? $r['status'];

            // ✅ Lấy giá từ booking, fallback sang schedule
            if (empty($r['price_adult'])) {
                $r['price_adult'] = $r['schedule_price_adult'] ?? 0;
            }
            if (empty($r['price_children'])) {
                $r['price_children'] = $r['schedule_price_children'] ?? 0;
            }

            // ✅ Nếu không có return_date trong booking, tính từ schedule
            if (empty($r['return_date']) && !empty($r['depart_date']) && !empty($r['duration_days'])) {
                $departTimestamp = strtotime($r['depart_date']);
                $duration = (int) $r['duration_days'];
                $returnTimestamp = strtotime("+{$duration} days", $departTimestamp);
                $r['return_date'] = date('Y-m-d', $returnTimestamp);
            }
        }

        return $r;
    }

    /** ========================
     *  ✅ TẠO BOOKING MỚI - CÓ TỰ ĐỘNG TẠO PAYMENT
     *  ======================== */
    /** ========================
     *  ✅ TẠO BOOKING MỚI - CÓ TỰ ĐỘNG TẠO PAYMENT
     *  ======================== */
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

            // =========================================================
            // 🔥 PHÂN BIỆT 2 LUỒNG: Tour có sẵn vs Tour theo yêu cầu
            // =========================================================

            if (!empty($data['tour_schedule_id'])) {
                // ✅ LUỒNG 1: Đặt tour theo lịch CÓ SẴN
                $schedule_id = (int) $data['tour_schedule_id'];
                $isCustomRequest = false;

                // Validate schedule tồn tại
                $stmt = $this->pdo->prepare("SELECT id FROM tour_schedule WHERE id = ? LIMIT 1");
                $stmt->execute([$schedule_id]);
                if (!$stmt->fetch()) {
                    throw new \Exception("Lịch tour không tồn tại");
                }

            } else {
                // ✅ LUỒNG 2: Tạo tour THEO YÊU CẦU (custom)
                $validateSchedule = $this->validateScheduleData($data);
                if ($validateSchedule) {
                    throw new \Exception(implode(', ', $validateSchedule));
                }

                // Xử lý tour_id
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

                // ✅ Tạo schedule CUSTOM
                $schedule_id = $this->createCustomSchedule($data, $tour_id);
                if (!$schedule_id) {
                    throw new \Exception("Không thể tạo lịch tour");
                }
                $isCustomRequest = true;
            }

            // =========================================================
            // TÍNH TOÁN GIÁ
            // =========================================================

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

            $total_amount = ($adults * $price_adult) + ($children * $price_children);

            // =========================================================
            // TẠO BOOKING
            // =========================================================

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

            // 🔥 TỰ ĐỘNG TẠO PAYMENT PENDING
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

            // ✅ Cập nhật seats cho tour thường
            if (!$isCustomRequest) {
                $this->updateSeats($schedule_id);
            }

            return ['ok' => true, 'booking_id' => $booking_id, 'payment_id' => $payment_id];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /** ========================
     *  ✅ CẬP NHẬT BOOKING - HỖ TRỢ 3 CHẾ ĐỘ SỬA
     *  ======================== */
    public function update($id, $data, $author_id = null)
    {
        // ✅ Tìm booking hiện tại
        $old = $this->find($id);
        if (!$old) {
            return ['ok' => false, 'errors' => ['Booking không tồn tại']];
        }

        // ✅ Xác định chế độ sửa từ frontend
        $editMode = $data['edit_mode'] ?? 'LIMITED';

        // =========================================================
        // 🎯 XỬ LÝ THEO CHẾ ĐỘ
        // =========================================================

        if ($editMode === 'VIEW_ONLY') {
            // 🔒 CHẾ ĐỘ CHỈ XEM - Chỉ cho sửa contact info
            return $this->updateContactInfoOnly($id, $data, $old, $author_id);
        }

        // ✅ Validate dữ liệu cơ bản
        $errors = $this->validateData($data);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        // ✅ Lấy giá trị từ form
        $adults = (int) ($data['adults'] ?? $old['adults']);
        $children = (int) ($data['children'] ?? $old['children']);
        $schedule_id = (int) ($data['tour_schedule_id'] ?? $old['tour_schedule_id']);
        $status = $data['status'] ?? $old['status'];

        // ✅ Validate schedule_id tồn tại
        if ($schedule_id <= 0) {
            return ['ok' => false, 'errors' => ['Lịch tour không hợp lệ']];
        }

        $stmt = $this->pdo->prepare("SELECT id FROM tour_schedule WHERE id = ? LIMIT 1");
        $stmt->execute([$schedule_id]);
        if (!$stmt->fetch()) {
            return ['ok' => false, 'errors' => ['Lịch tour không tồn tại trong hệ thống']];
        }

        // ✅ Kiểm tra tour custom
        $isCustom = $this->isCustomRequest($schedule_id);

        // =========================================================
        // 🔥 LOGIC THEO CHẾ ĐỘ SỬA
        // =========================================================

        $reasons = [];

        if ($editMode === 'FULL') {
            // ✅ CHẾ ĐỘ ĐẦY ĐỦ - Cho phép sửa tất cả
            $reasons[] = $isCustom ? "Tour theo yêu cầu" : "Chế độ sửa đầy đủ";

        } elseif ($editMode === 'LIMITED') {
            // ⚠️ CHẾ ĐỘ GIỚI HẠN - Kiểm tra thay đổi

            // Check 1: Đổi tour
            if ($schedule_id != $old['tour_schedule_id']) {
                // Cho phép đổi tour, nhưng phải check capacity
                $reasons[] = "Đổi sang tour mới (ID: {$schedule_id})";

                // Validate tour mới có còn chỗ không
                if (!$isCustom) {
                    if (!$this->checkCapacity($schedule_id, $adults, $children)) {
                        return [
                            'ok' => false,
                            'errors' => [
                                '❌ <strong>Tour mới không đủ chỗ!</strong><br>' .
                                'Vui lòng chọn tour khác hoặc giảm số lượng người.'
                            ]
                        ];
                    }
                }
            }

            // Check 2: Đổi số người
            if ($adults != $old['adults'] || $children != $old['children']) {
                $reasons[] = "Thay đổi số người: {$old['adults']}NL+{$old['children']}TE → {$adults}NL+{$children}TE";

                // Validate capacity
                if (!$isCustom) {
                    if (!$this->checkCapacity($schedule_id, $adults, $children, $id)) {
                        return [
                            'ok' => false,
                            'errors' => [
                                '❌ <strong>Không đủ chỗ trống!</strong><br>' .
                                'Tour này chỉ còn <strong>' .
                                $this->getAvailableSeats($schedule_id, $id) .
                                '</strong> chỗ.<br>' .
                                '💡 <strong>Giải pháp:</strong><br>' .
                                '&nbsp;&nbsp;&nbsp;• Giảm số lượng người<br>' .
                                '&nbsp;&nbsp;&nbsp;• Chọn tour khác<br>' .
                                '&nbsp;&nbsp;&nbsp;• Hủy booking này và tạo booking mới'
                            ]
                        ];
                    }
                }
            }

            // Check 3: Đổi giá
            $priceAdultOld = (float) $old['price_adult'];
            $priceAdultNew = (float) ($data['price_adult'] ?? $priceAdultOld);
            if (abs($priceAdultNew - $priceAdultOld) > 0.01) {
                $reasons[] = "Điều chỉnh giá: " . number_format($priceAdultOld) . "đ → " . number_format($priceAdultNew) . "đ";
            }
        }

        // =========================================================
        // ✅ VALIDATE LOGIC NGHIỆP VỤ CHUNG
        // =========================================================

        // Check 1: Chuyển sang COMPLETED → Phải thanh toán đủ
        if ($status === 'COMPLETED' && $old['status'] !== 'COMPLETED') {
            $paymentStatus = $this->getPaymentStatus($id);
            if ($paymentStatus !== 'FULL_PAID') {
                return [
                    'ok' => false,
                    'errors' => [
                        '❌ <strong>Không thể chuyển sang HOÀN TẤT</strong><br>' .
                        '💰 Trạng thái thanh toán: <strong>' .
                        match ($paymentStatus) {
                            'DEPOSIT_PAID' => 'Đã cọc (chưa đủ)',
                            'PENDING' => 'Chưa thanh toán',
                            default => $paymentStatus
                        } . '</strong><br>' .
                        '💡 Vui lòng tạo payment để thanh toán đủ trước.'
                    ]
                ];
            }
        }

        // Check 2: Chuyển sang HỦY
        if ($status === 'CANCELED' && $old['status'] !== 'CANCELED') {
            $reasons[] = "Admin chủ động HỦY booking";
        }

        // =========================================================
        // TÍNH TOÁN GIÁ
        // =========================================================

        $price_adult = (float) ($data['price_adult'] ?? $old['price_adult']);
        $price_children = (float) ($data['price_children'] ?? $old['price_children']);
        $total_amount = ($adults * $price_adult) + ($children * $price_children);

        // =========================================================
        // LƯU DATABASE
        // =========================================================

        try {
            $this->pdo->beginTransaction();

            // Update booking
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

            // ✅ GHI LOG CHI TIẾT
            $changes = [];

            // Log chế độ sửa
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

            // Log các thay đổi cụ thể
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

            if ($old['contact_name'] !== ($data['contact_name'] ?? $old['contact_name'])) {
                $changes[] = "Tên khách: {$old['contact_name']} → " . ($data['contact_name'] ?? '');
            }

            if ($old['contact_phone'] !== ($data['contact_phone'] ?? $old['contact_phone'])) {
                $changes[] = "SĐT: {$old['contact_phone']} → " . ($data['contact_phone'] ?? '');
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

            // ✅ CẬP NHẬT SEATS (chỉ với tour thường)
            if (!$isCustom) {
                if ($old['tour_schedule_id'] !== $schedule_id) {
                    // Đổi tour → Update cả 2 tours
                    $this->updateSeats($old['tour_schedule_id']);
                    $this->updateSeats($schedule_id);
                } else {
                    // Chỉ đổi số người → Update tour hiện tại
                    $this->updateSeats($schedule_id);
                }
            }

            return ['ok' => true, 'message' => '✅ Cập nhật thành công!'];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /** ========================
     *  🔒 UPDATE CHỈ CONTACT INFO (VIEW_ONLY MODE)
     *  ======================== */
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

        // ✅ Kiểm tra có thể chuyển sang READY không
        $validation = $this->validateStatusTransition($b['status'], 'READY');
        if (!$validation['ok']) {
            return $validation;
        }

        // ✅ Kiểm tra đã thanh toán chưa
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

    // ✅ THÊM: startTour() - Bắt đầu tour
    public function startTour($booking_id, $author_id = null)
    {
        $b = $this->find($booking_id);
        if (!$b) {
            return ['ok' => false, 'errors' => ['Booking không tồn tại']];
        }

        // ✅ Kiểm tra có thể chuyển sang IN_PROGRESS không
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
    /** ========================
     *  ✅ ĐÁNH DẤU HOÀN TẤT (Khi tour kết thúc)
     *  ======================== */
    public function markAsCompleted($booking_id, $author_id = null)
    {
        $b = $this->find($booking_id);
        if (!$b) {
            return ['ok' => false, 'errors' => ['Booking không tồn tại']];
        }

        // ✅ Kiểm tra có thể chuyển sang COMPLETED không
        $validation = $this->validateStatusTransition($b['status'], 'COMPLETED');
        if (!$validation['ok']) {
            return $validation;
        }

        // ✅ Kiểm tra đã thanh toán đủ chưa
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

        // ✅ Kiểm tra có thể chuyển sang REFUNDED không
        $validation = $this->validateStatusTransition($b['status'], 'REFUNDED');
        if (!$validation['ok']) {
            return $validation;
        }

        try {
            $this->pdo->beginTransaction();

            // ✅ Chuyển sang REFUNDED
            $this->pdo->prepare("UPDATE bookings SET status = 'REFUNDED' WHERE id = ?")
                ->execute([$booking_id]);

            // ✅ Tạo payment hoàn tiền (nếu có số tiền)
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
        // ✅ BỎ QUA CHECK cho tour theo yêu cầu
        if ($this->isCustomRequest($schedule_id)) {
            return true; // Tour custom = không giới hạn chỗ
        }

        // ✅ Lấy tổng chỗ của schedule
        $stmt = $this->pdo->prepare("SELECT seats_total FROM tour_schedule WHERE id = ?");
        $stmt->execute([$schedule_id]);
        $sc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sc) {
            return false; // Schedule không tồn tại
        }

        $seats_total = (int) $sc['seats_total'];

        // ✅ Nếu seats_total = 0 hoặc NULL → Coi như không giới hạn
        if ($seats_total <= 0) {
            return true;
        }

        // ✅ Tính số chỗ đã book (trừ booking hiện tại nếu đang update)
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

        // ✅ Check: Tổng sau khi thêm có vượt không?
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

            // ✅ Chuyển về boolean rõ ràng
            return (int) $row['is_custom_request'] === 1;

        } catch (\Throwable $e) {
            error_log("isCustomRequest Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ LẤY TRẠNG THÁI THANH TOÁN CỦA BOOKING
     */
    public function getPaymentStatus($booking_id)
    {
        return $this->paymentModel->getPaymentStatus($booking_id);
    }

    /**
     * 💰 LẤY TỔNG TIỀN ĐÃ THANH TOÁN
     */
    public function getTotalPaid($booking_id)
    {
        return $this->paymentModel->getTotalPaid($booking_id);
    }

    // ============ PRIVATE HELPERS ============

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
               -- ✅ THÊM: Check xem đã quá ngày chưa
               CASE 
                   WHEN ts.depart_date < CURDATE() THEN 1
                   ELSE 0
               END AS is_past_date
            FROM tour_schedule ts
            JOIN tours t ON t.id = ts.tour_id
            LEFT JOIN tour_category c ON c.id = t.category_id
            WHERE ts.status = 'OPEN'
              -- ❌ BỎ ĐIỀU KIỆN NÀY để hiện tất cả tour OPEN
              -- AND ts.depart_date >= CURDATE()  
            ORDER BY 
              is_past_date ASC,           
              ts.is_custom_request ASC, 
              ts.depart_date ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
