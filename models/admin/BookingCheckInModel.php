<?php
// models/admin/BookingCheckInModel.php

class BookingCheckInModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    /**
     * Lấy danh sách khách trong booking để check-in
     */
    public function getBookingGuests($booking_id)
    {
        $sql = "SELECT 
                    b.id as booking_id,
                    b.booking_code,
                    b.contact_name,
                    b.contact_phone,
                    b.adults,
                    b.children,
                    b.total_people,
                    ts.depart_date,
                    t.title as tour_name,
                    COALESCE(bc.checked_in_count, 0) as checked_in_count,
                    COALESCE(bc.checked_in_adults, 0) as checked_in_adults,
                    COALESCE(bc.checked_in_children, 0) as checked_in_children
                FROM bookings b
                JOIN tour_schedule ts ON b.tour_schedule_id = ts.id
                JOIN tours t ON ts.tour_id = t.id
                LEFT JOIN (
                    SELECT 
                        booking_id,
                        COUNT(*) as checked_in_count,
                        SUM(CASE WHEN guest_type = 'ADULT' THEN 1 ELSE 0 END) as checked_in_adults,
                        SUM(CASE WHEN guest_type = 'CHILD' THEN 1 ELSE 0 END) as checked_in_children
                    FROM booking_check_in
                    WHERE check_in_status = 'PRESENT'
                    GROUP BY booking_id
                ) bc ON b.id = bc.booking_id
                WHERE b.id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$booking_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách check-in chi tiết
     */
    public function getCheckInList($booking_id)
    {
        $sql = "SELECT * FROM booking_check_in 
                WHERE booking_id = ? 
                ORDER BY guest_type DESC, check_in_time ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$booking_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check-in khách hàng
     */
    public function checkInGuest($data)
    {
        $errors = [];

        // Validate
        if (empty($data['booking_id'])) {
            $errors[] = "Booking ID không hợp lệ";
        }
        if (empty($data['guest_name'])) {
            $errors[] = "Tên khách không được để trống";
        }
        if (!in_array($data['guest_type'] ?? '', ['ADULT', 'CHILD'])) {
            $errors[] = "Loại khách không hợp lệ";
        }

        if (!empty($errors)) {
            return ['ok' => false, 'errors' => $errors];
        }

        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO booking_check_in 
                    (booking_id, guest_name, guest_type, id_number, phone, 
                     check_in_status, check_in_time, notes)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['booking_id'],
                $data['guest_name'],
                $data['guest_type'],
                $data['id_number'] ?? null,
                $data['phone'] ?? null,
                $data['check_in_status'] ?? 'PRESENT',
                $data['notes'] ?? null
            ]);

            // Log vào tour_logs
            $stmt = $this->pdo->prepare("
                INSERT INTO tour_logs (booking_id, entry_type, content, created_at)
                VALUES (?, 'CHECK_IN', ?, NOW())
            ");
            $stmt->execute([
                $data['booking_id'],
                "Check-in: {$data['guest_name']} ({$data['guest_type']})"
            ]);

            $this->pdo->commit();

            return ['ok' => true, 'id' => $this->pdo->lastInsertId()];

        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /**
     * Cập nhật trạng thái check-in
     */
    public function updateCheckInStatus($id, $status, $notes = null)
    {
        $validStatuses = ['PRESENT', 'ABSENT', 'LATE'];
        if (!in_array($status, $validStatuses)) {
            return ['ok' => false, 'errors' => ['Trạng thái không hợp lệ']];
        }

        try {
            $sql = "UPDATE booking_check_in 
                    SET check_in_status = ?, notes = ?, updated_at = NOW()
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$status, $notes, $id]);

            return ['ok' => true];

        } catch (\Exception $e) {
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /**
     * Xóa check-in (nếu nhầm)
     */
    public function deleteCheckIn($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM booking_check_in WHERE id = ?");
            $stmt->execute([$id]);
            return ['ok' => true];
        } catch (\Exception $e) {
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }

    /**
     * Thống kê check-in theo tour schedule
     */
    public function getCheckInStatsBySchedule($schedule_id)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT b.id) as total_bookings,
                    SUM(b.total_people) as total_guests,
                    COUNT(DISTINCT CASE WHEN bci.check_in_status = 'PRESENT' THEN bci.id END) as checked_in,
                    COUNT(DISTINCT CASE WHEN bci.check_in_status = 'ABSENT' THEN bci.id END) as absent
                FROM bookings b
                LEFT JOIN booking_check_in bci ON b.id = bci.booking_id
                WHERE b.tour_schedule_id = ?
                  AND b.status NOT IN ('CANCELED')";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$schedule_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check-in nhanh toàn bộ booking
     */
    public function quickCheckInAll($booking_id)
    {
        try {
            $this->pdo->beginTransaction();

            // Lấy thông tin booking
            $booking = $this->getBookingGuests($booking_id);
            if (!$booking) {
                throw new \Exception("Booking không tồn tại");
            }

            // Check-in người lớn
            for ($i = 1; $i <= $booking['adults']; $i++) {
                $this->checkInGuest([
                    'booking_id' => $booking_id,
                    'guest_name' => $booking['contact_name'] . ($i > 1 ? " #{$i}" : ""),
                    'guest_type' => 'ADULT',
                    'check_in_status' => 'PRESENT',
                    'notes' => 'Check-in nhanh tự động'
                ]);
            }

            // Check-in trẻ em
            for ($i = 1; $i <= $booking['children']; $i++) {
                $this->checkInGuest([
                    'booking_id' => $booking_id,
                    'guest_name' => "Trẻ em #{$i}",
                    'guest_type' => 'CHILD',
                    'check_in_status' => 'PRESENT',
                    'notes' => 'Check-in nhanh tự động'
                ]);
            }

            $this->pdo->commit();
            return ['ok' => true];

        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['ok' => false, 'errors' => [$e->getMessage()]];
        }
    }
}