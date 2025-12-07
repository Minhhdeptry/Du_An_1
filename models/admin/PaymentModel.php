<?php
class PaymentModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    // TRANG 1: lấy tất cả thanh toán (kèm booking + user)
    public function getAllPayments()
    {
        $sql = "SELECT p.*, b.booking_code AS booking_code, u.full_name AS customer_name
                FROM payments p
                LEFT JOIN bookings b ON p.booking_id = b.id
                LEFT JOIN users u ON b.user_id = u.id
                ORDER BY p.id DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    // TRANG 2: lịch sử theo booking
    public function getPaymentsByBooking($booking_id)
    {
        $sql = "SELECT p.*, b.booking_code AS booking_code, u.full_name AS customer_name
                FROM payments p
                LEFT JOIN bookings b ON p.booking_id = b.id
                LEFT JOIN users u ON b.user_id = u.id
                WHERE p.booking_id = :booking_id
                ORDER BY p.id DESC";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(['booking_id' => $booking_id]);
        return $stm->fetchAll();
    }

    public function getPaymentsByBookingIds(array $bookingIds)
    {
        if (!$bookingIds)
            return [];
        $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));
        $sql = "SELECT * FROM payments WHERE booking_id IN ($placeholders) ORDER BY id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bookingIds);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($payments as $p) {
            $result[$p['booking_id']][] = $p;
        }
        return $result;
    }

    // models/admin/PaymentModel.php

    public function getPaymentById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy thông tin booking
    public function getBookingInfo($booking_id)
    {
        $sql = "SELECT b.*, u.full_name AS user_name, u.email AS customer_email
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.id
                WHERE b.id = :id LIMIT 1";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(['id' => $booking_id]);
        return $stm->fetch();
    }

    public function confirmPayment($id)
    {
        $sql = "UPDATE payments SET status='SUCCESS' WHERE id=:id";
        $stm = $this->pdo->prepare($sql);
        return $stm->execute(['id' => $id]);
    }

    public function addPayment($data)
    {
        $sql = "INSERT INTO payments (booking_id, amount, method, transaction_code, paid_at, status, type)
            VALUES (:booking_id, :amount, :method, :transaction_code, :paid_at, :status, :type)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'booking_id' => $data['booking_id'],
            'amount' => $data['amount'],
            'method' => $data['method'],
            'transaction_code' => $data['transaction_code'],
            'paid_at' => $data['paid_at'],
            'status' => 'PENDING',               // mặc định
            'type' => $data['type'] ?? 'FULL'    // <<<<<< THÊM Ở ĐÂY
        ]);
    }

    public function cancelPayment($id)
    {
        $sql = "UPDATE payments SET status = 'REFUNDED' WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

}
?>