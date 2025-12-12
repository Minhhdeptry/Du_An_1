<?php
// models/admin/BookingCustomerModel.php

class BookingCustomerModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    /**
     * Lấy danh sách khách của 1 booking
     */
    public function getByBooking($booking_id)
    {
        $sql = "SELECT * FROM booking_customers 
                WHERE booking_id = ? 
                ORDER BY customer_type, id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$booking_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin 1 khách
     */
    public function find($id)
    {
        $sql = "SELECT bc.*, b.booking_code, b.tour_schedule_id
                FROM booking_customers bc
                JOIN bookings b ON bc.booking_id = b.id
                WHERE bc.id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm khách vào booking
     */
    public function store($data)
    {
        $sql = "INSERT INTO booking_customers 
                (booking_id, customer_type, full_name, date_of_birth, 
                 id_number, phone, email, gender, nationality, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $data['booking_id'],
            $data['customer_type'],
            $data['full_name'],
            $data['date_of_birth'] ?? null,
            $data['id_number'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['gender'] ?? null,
            $data['nationality'] ?? 'Việt Nam',
            $data['notes'] ?? null
        ]);
    }

    /**
     * Cập nhật thông tin khách
     */
    public function update($data)
    {
        $sql = "UPDATE booking_customers SET
                customer_type = ?,
                full_name = ?,
                date_of_birth = ?,
                id_number = ?,
                phone = ?,
                email = ?,
                gender = ?,
                nationality = ?,
                notes = ?
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $data['customer_type'],
            $data['full_name'],
            $data['date_of_birth'] ?? null,
            $data['id_number'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['gender'] ?? null,
            $data['nationality'] ?? 'Việt Nam',
            $data['notes'] ?? null,
            $data['id']
        ]);
    }

    /**
     * Xóa khách
     */
    public function delete($id)
    {
        $sql = "DELETE FROM booking_customers WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Check-in khách
     */
    public function checkIn($id)
    {
        $sql = "UPDATE booking_customers 
                SET checked_in = 1, checked_in_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Hủy check-in
     */
    public function undoCheckIn($id)
    {
        $sql = "UPDATE booking_customers 
                SET checked_in = 0, checked_in_at = NULL 
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Đếm số khách theo loại trong booking
     */
    public function countByType($booking_id)
    {
        $sql = "SELECT 
                    customer_type,
                    COUNT(*) as count
                FROM booking_customers
                WHERE booking_id = ?
                GROUP BY customer_type";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$booking_id]);
        
        $result = [
            'ADULT' => 0,
            'CHILD' => 0,
            'INFANT' => 0
        ];
        
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['customer_type']] = (int)$row['count'];
        }
        
        return $result;
    }

    /**
     * Kiểm tra số lượng khách đã nhập vs số lượng trong booking
     */
    public function validateCount($booking_id)
    {
        // Lấy số khách trong booking
        $sql = "SELECT adults, children FROM bookings WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            return ['valid' => false, 'message' => 'Booking không tồn tại'];
        }
        
        // Đếm số khách đã nhập
        $counts = $this->countByType($booking_id);
        
        $expected_adults = (int)$booking['adults'];
        $expected_children = (int)$booking['children'];
        $actual_adults = $counts['ADULT'];
        $actual_children = $counts['CHILD'] + $counts['INFANT'];
        
        if ($actual_adults !== $expected_adults || $actual_children !== $expected_children) {
            return [
                'valid' => false,
                'message' => "Số khách không khớp! Cần: {$expected_adults} NL, {$expected_children} TE. Có: {$actual_adults} NL, {$actual_children} TE."
            ];
        }
        
        return ['valid' => true, 'message' => 'OK'];
    }

    /**
     * Lấy danh sách khách cho HDV check-in
     */
    public function getForCheckIn($tour_schedule_id)
    {
        $sql = "SELECT 
                    bc.*,
                    b.booking_code,
                    b.contact_name,
                    b.contact_phone,
                    b.status as booking_status
                FROM booking_customers bc
                JOIN bookings b ON bc.booking_id = b.id
                WHERE b.tour_schedule_id = ?
                  AND b.status IN ('DEPOSIT_PAID', 'COMPLETED')
                ORDER BY b.booking_code, bc.customer_type, bc.full_name";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tour_schedule_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thống kê check-in
     */
    public function getCheckInStats($tour_schedule_id)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN checked_in = 1 THEN 1 ELSE 0 END) as checked_in
                FROM booking_customers bc
                JOIN bookings b ON bc.booking_id = b.id
                WHERE b.tour_schedule_id = ?
                  AND b.status IN ('DEPOSIT_PAID', 'COMPLETED')";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tour_schedule_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}