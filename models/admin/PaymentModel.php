<?php
// models/admin/PaymentModel.php

class PaymentModel
{
    private $pdo;

    public static $statusLabels = [
        'PENDING' => 'Chờ thanh toán',
        'SUCCESS' => 'Thành công',
        'FAILED' => 'Thất bại',
        'REFUNDED' => 'Đã hoàn tiền',
    ];

    public static $typeLabels = [
        'DEPOSIT' => 'Đặt cọc',
        'FULL' => 'Thanh toán đủ',
        'REMAINING' => 'Thanh toán còn lại',
    ];

    public static $methodLabels = [
        'CASH' => 'Tiền mặt',
        'BANK_TRANSFER' => 'Chuyển khoản',
        'CREDIT_CARD' => 'Thẻ tín dụng',
        'MOMO' => 'MoMo',
        'VNPAY' => 'VNPay',
        'ZALOPAY' => 'ZaloPay',
    ];

    public function __construct($pdo = null)
    {
        if ($pdo) {
            $this->pdo = $pdo;  // Dùng chung kết nối
        } else {
            require_once "./commons/function.php";
            $this->pdo = connectDB();
        }
    }


    /** ========================
     *  📋 LẤY TẤT CẢ PAYMENTS
     *  ======================== */
    public function getAll()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, 
                       b.booking_code, 
                       b.contact_name,
                       b.total_amount as booking_total
                FROM payments p
                LEFT JOIN bookings b ON p.booking_id = b.id
                ORDER BY p.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("GetAllPayments Error: " . $e->getMessage());
            return [];
        }
    }

    /** ========================
     *  🔥 TỰ ĐỘNG TẠO PAYMENT KHI TẠO BOOKING
     *  ======================== */
    public function createInitialPayment($booking_id, $total_amount)
    {
        if (!$booking_id || !$total_amount) {
            error_log("Invalid data for payment");
            return null;
        }

        try {
            $payment_code = $this->generatePaymentCode();

            $sql = "INSERT INTO payments 
                (payment_code, booking_id, amount, type, method, status, created_at)
                VALUES (:code, :booking_id, :amount, 'FULL', 'BANK_TRANSFER', 'PENDING', NOW())";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':code' => $payment_code,
                ':booking_id' => $booking_id,
                ':amount' => $total_amount,
            ]);

            return $this->pdo->lastInsertId();

        } catch (\Throwable $e) {
            error_log("CreateInitialPayment Error: " . $e->getMessage());
            return null;
        }
    }


    /** ========================
     *  💰 TẠO PAYMENT THỦ CÔNG (từ Admin)
     *  ======================== */
    public function create($data)
    {
        $errors = $this->validateData($data);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        try {
            $this->pdo->beginTransaction();

            $payment_code = $this->generatePaymentCode();
            $booking_id = (int) $data['booking_id'];
            $amount = (float) $data['amount'];
            $type = $data['type'] ?? 'FULL';
            $method = $data['method'] ?? 'CASH';
            $status = $data['status'] ?? 'SUCCESS';
            $paid_at = !empty($data['paid_at']) ? $data['paid_at'] : date('Y-m-d H:i:s');
            $note = $data['note'] ?? '';

            $stmt = $this->pdo->prepare("
                INSERT INTO payments 
                (payment_code, booking_id, amount, type, method, status, paid_at, note, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $payment_code,
                $booking_id,
                $amount,
                $type,
                $method,
                $status,
                $paid_at,
                $note
            ]);

            $payment_id = $this->pdo->lastInsertId();

            // ✅ TỰ ĐỘNG CẬP NHẬT TRẠNG THÁI BOOKING (chỉ khi payment SUCCESS)
            if ($status === 'SUCCESS') {
                $this->updateBookingStatusAuto($booking_id);
            }

            $this->pdo->commit();

            return ['ok' => true, 'payment_id' => $payment_id];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("CreatePayment Error: " . $e->getMessage());
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /** ========================
     *  📝 LẤY DANH SÁCH PAYMENTS CỦA BOOKING
     *  ======================== */
    public function getByBooking($booking_id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM payments 
                WHERE booking_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$booking_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("GetByBooking Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ TẠO PAYMENT HOÀN TIỀN (số âm)
     */
    public function createRefundPayment($booking_id, $refundAmount, $reason = '')
    {
        try {
            $paymentCode = 'REF-' . date('ymd') . '-' . rand(1000, 9999);

            $stmt = $this->pdo->prepare("
            INSERT INTO payments 
            (booking_id, payment_code, amount, payment_method, payment_date, status, notes)
            VALUES (?, ?, ?, 'REFUND', NOW(), 'COMPLETED', ?)
        ");

            // ✅ Số tiền âm để đánh dấu là hoàn tiền
            $stmt->execute([
                $booking_id,
                $paymentCode,
                -abs($refundAmount), // Luôn âm
                $reason ?: 'Hoàn tiền'
            ]);

            return $this->pdo->lastInsertId();

        } catch (\Throwable $e) {
            error_log("CreateRefundPayment Error: " . $e->getMessage());
            return null;
        }
    }
    /** ========================
     *  📊 TÍNH TỔNG TIỀN ĐÃ THANH TOÁN
     *  ======================== */
    public function getTotalPaid($booking_id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT SUM(amount) as total
                FROM payments
                WHERE booking_id = ? AND status = 'SUCCESS'
            ");
            $stmt->execute([$booking_id]);
            return (float) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Throwable $e) {
            error_log("GetTotalPaid Error: " . $e->getMessage());
            return 0;
        }
    }

    /** ========================
     *  🔍 KIỂM TRA TRẠNG THÁI THANH TOÁN
     *  ======================== */
    public function getPaymentStatus($booking_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT total_amount FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_booking = (float) ($booking['total_amount'] ?? 0);

            if ($total_booking <= 0) {
                return 'PENDING';
            }

            $stmt = $this->pdo->prepare("
                SELECT SUM(amount) as total_paid
                FROM payments
                WHERE booking_id = ? AND status = 'SUCCESS'
            ");
            $stmt->execute([$booking_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_paid = (float) ($result['total_paid'] ?? 0);

            if ($total_paid == 0) {
                return 'PENDING';
            } elseif ($total_paid >= $total_booking) {
                return 'FULL_PAID';
            } else {
                return 'DEPOSIT_PAID';
            }

        } catch (\Throwable $e) {
            error_log("GetPaymentStatus Error: " . $e->getMessage());
            return 'PENDING';
        }
    }

    /** ========================
     *  🔄 TỰ ĐỘNG CẬP NHẬT TRẠNG THÁI BOOKING DỰA VÀO PAYMENT
     *  ✅ Logic mới: 4 trạng thái (PENDING, DEPOSIT_PAID, COMPLETED, CANCELED)
     *  ======================== */
    private function updateBookingStatusAuto($booking_id)
    {
        try {
            $paymentStatus = $this->getPaymentStatus($booking_id);

            // Lấy booking hiện tại
            $stmt = $this->pdo->prepare("SELECT status, total_amount FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking)
                return;

            // Không update nếu đã COMPLETED hoặc CANCELED
            if (in_array($booking['status'], ['COMPLETED', 'CANCELED'])) {
                return;
            }

            // ✅ Logic chuyển trạng thái theo 4 trạng thái mới
            $newStatus = null;
            $logMessage = null;

            if ($paymentStatus === 'FULL_PAID') {
                // Đã thanh toán đủ → Hoàn tất
                $newStatus = 'COMPLETED';
                $logMessage = "Booking chuyển sang HOÀN TẤT (đã thanh toán đủ)";
            } elseif ($paymentStatus === 'DEPOSIT_PAID') {
                // Đã cọc
                $newStatus = 'DEPOSIT_PAID';
                $logMessage = "Booking chuyển sang ĐÃ CỌC (đã thanh toán một phần)";
            }

            // Chỉ update nếu có thay đổi status
            if ($newStatus && $newStatus !== $booking['status']) {
                $stmt = $this->pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $booking_id]);

                // Ghi log
                $stmt = $this->pdo->prepare("
                    INSERT INTO tour_logs (booking_id, entry_type, content, created_at)
                    VALUES (?, 'NOTE', ?, NOW())
                ");
                $stmt->execute([$booking_id, $logMessage]);
            }

        } catch (\Throwable $e) {
            error_log("UpdateBookingStatusAuto Error: " . $e->getMessage());
        }
    }

    /** ========================
     *  🗑️ XÓA PAYMENT
     *  ======================== */
    public function delete($payment_id)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT booking_id FROM payments WHERE id = ?");
            $stmt->execute([$payment_id]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                $this->pdo->rollBack();
                return ['ok' => false, 'errors' => ['Payment không tồn tại']];
            }

            $booking_id = $payment['booking_id'];

            $stmt = $this->pdo->prepare("DELETE FROM payments WHERE id = ?");
            $stmt->execute([$payment_id]);

            // ✅ Cập nhật lại trạng thái booking sau khi xóa payment
            $this->updateBookingStatusAuto($booking_id);

            $this->pdo->commit();

            return ['ok' => true];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("DeletePayment Error: " . $e->getMessage());
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /** ========================
     *  ✏️ CẬP NHẬT PAYMENT
     *  ======================== */
    public function update($payment_id, $data)
    {
        $errors = $this->validateData($data);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                UPDATE payments SET
                    amount = ?,
                    type = ?,
                    method = ?,
                    status = ?,
                    paid_at = ?,
                    note = ?
                WHERE id = ?
            ");

            $stmt->execute([
                (float) $data['amount'],
                $data['type'] ?? 'FULL',
                $data['method'] ?? 'CASH',
                $data['status'] ?? 'SUCCESS',
                $data['paid_at'] ?? date('Y-m-d H:i:s'),
                $data['note'] ?? '',
                $payment_id
            ]);

            $stmt = $this->pdo->prepare("SELECT booking_id FROM payments WHERE id = ?");
            $stmt->execute([$payment_id]);
            $booking_id = $stmt->fetch(PDO::FETCH_ASSOC)['booking_id'] ?? null;

            // ✅ TỰ ĐỘNG CẬP NHẬT TRẠNG THÁI BOOKING
            if ($booking_id) {
                $this->updateBookingStatusAuto($booking_id);
            }

            $this->pdo->commit();

            return ['ok' => true];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("UpdatePayment Error: " . $e->getMessage());
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /** ========================
     *  🔍 TÌM PAYMENT THEO ID
     *  ======================== */
    public function find($payment_id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, 
                       b.booking_code, 
                       b.contact_name 
                FROM payments p
                LEFT JOIN bookings b ON p.booking_id = b.id
                WHERE p.id = ? 
                LIMIT 1
            ");
            $stmt->execute([$payment_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("FindPayment Error: " . $e->getMessage());
            return null;
        }
    }

    /** ========================
     *  🎲 TẠO MÃ PAYMENT
     *  ======================== */
    private function generatePaymentCode(): string
    {
        return 'PAY' . date('ymdHis') . rand(100, 999);
    }

    /** ========================
     *  ✅ VALIDATE DỮ LIỆU
     *  ======================== */
    private function validateData(array $data): array
    {
        $errors = [];

        if (empty($data['booking_id'])) {
            $errors[] = "Booking ID không được để trống.";
        }

        $amount = (float) ($data['amount'] ?? 0);
        if ($amount <= 0) {
            $errors[] = "Số tiền phải lớn hơn 0.";
        }

        $validTypes = ['DEPOSIT', 'FULL', 'REMAINING'];
        if (!empty($data['type']) && !in_array($data['type'], $validTypes)) {
            $errors[] = "Loại thanh toán không hợp lệ.";
        }

        $validMethods = ['CASH', 'BANK_TRANSFER', 'CREDIT_CARD', 'MOMO', 'VNPAY', 'ZALOPAY'];
        if (!empty($data['method']) && !in_array($data['method'], $validMethods)) {
            $errors[] = "Phương thức thanh toán không hợp lệ.";
        }

        $validStatuses = ['PENDING', 'SUCCESS', 'FAILED', 'REFUNDED'];
        if (!empty($data['status']) && !in_array($data['status'], $validStatuses)) {
            $errors[] = "Trạng thái thanh toán không hợp lệ.";
        }

        return $errors;
    }
}