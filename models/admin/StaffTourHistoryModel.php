<?php
// ============================================
// FILE 1: models/admin/StaffTourHistoryModel.php
// ============================================

class StaffTourHistoryModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    /**
     * Lấy lịch sử tour của HDV
     */
    public function getStaffHistory($staff_id, $limit = 50)
    {
        $sql = "SELECT 
                    sth.*,
                    t.code AS tour_code,
                    t.title AS tour_name,
                    ts.depart_date,
                    ts.return_date,
                    ts.status AS tour_status,
                    COUNT(b.id) AS total_bookings,
                    SUM(b.adults + b.children) AS total_customers
                FROM staff_tour_history sth
                JOIN tour_schedule ts ON ts.id = sth.tour_schedule_id
                JOIN tours t ON t.id = ts.tour_id
                LEFT JOIN bookings b ON b.tour_schedule_id = ts.id
                WHERE sth.staff_id = ?
                GROUP BY sth.id
                ORDER BY ts.depart_date DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$staff_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm HDV vào tour
     */
    public function assignStaffToTour($staff_id, $tour_schedule_id, $role = 'GUIDE')
    {
        try {
            // Check conflict
            if (!$this->checkAvailability($staff_id, $tour_schedule_id)) {
                return ['ok' => false, 'error' => 'HDV đã có lịch trùng!'];
            }

            $sql = "INSERT INTO staff_tour_history 
                    (staff_id, tour_schedule_id, role, status)
                    VALUES (?, ?, ?, 'SCHEDULED')";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$staff_id, $tour_schedule_id, $role]);

            // Update tour_schedule
            $this->updateTourScheduleGuide($tour_schedule_id, $staff_id, $role);

            return ['ok' => true];
        } catch (PDOException $e) {
            error_log("assignStaffToTour Error: " . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cập nhật guide_id trong tour_schedule
     */
    private function updateTourScheduleGuide($tour_schedule_id, $staff_id, $role)
    {
        $column = ($role === 'GUIDE') ? 'guide_id' : 'assistant_guide_id';
        
        $sql = "UPDATE tour_schedule SET $column = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$staff_id, $tour_schedule_id]);
    }

    /**
     * Kiểm tra HDV có rảnh không
     */
    public function checkAvailability($staff_id, $tour_schedule_id)
    {
        // Lấy thông tin tour cần check
        $stmt = $this->pdo->prepare("SELECT depart_date, return_date FROM tour_schedule WHERE id = ?");
        $stmt->execute([$tour_schedule_id]);
        $tour = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tour) return false;

        // Check conflict
        $sql = "SELECT COUNT(*) as count
                FROM staff_tour_history sth
                JOIN tour_schedule ts ON ts.id = sth.tour_schedule_id
                WHERE sth.staff_id = ?
                  AND sth.status = 'SCHEDULED'
                  AND sth.tour_schedule_id != ?
                  AND (
                      (ts.depart_date BETWEEN ? AND ?)
                      OR (ts.return_date BETWEEN ? AND ?)
                      OR (? BETWEEN ts.depart_date AND ts.return_date)
                      OR (? BETWEEN ts.depart_date AND ts.return_date)
                  )";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $staff_id, 
            $tour_schedule_id,
            $tour['depart_date'], 
            $tour['return_date'],
            $tour['depart_date'], 
            $tour['return_date'],
            $tour['depart_date'],
            $tour['return_date']
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] == 0;
    }

    /**
     * Đánh giá HDV sau tour
     */
    public function rateStaff($staff_tour_history_id, $rating, $feedback, $admin_notes = null)
    {
        $sql = "UPDATE staff_tour_history 
                SET performance_rating = ?,
                    customer_feedback = ?,
                    admin_notes = ?,
                    status = 'COMPLETED'
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$rating, $feedback, $admin_notes, $staff_tour_history_id]);
    }

    /**
     * Lấy tour sắp tới của HDV
     */
    public function getUpcomingTours($staff_id, $limit = 5)
    {
        $sql = "SELECT 
                    sth.*,
                    t.code AS tour_code,
                    t.title AS tour_name,
                    ts.depart_date,
                    ts.return_date,
                    COUNT(b.id) AS total_bookings
                FROM staff_tour_history sth
                JOIN tour_schedule ts ON ts.id = sth.tour_schedule_id
                JOIN tours t ON t.id = ts.tour_id
                LEFT JOIN bookings b ON b.tour_schedule_id = ts.id
                WHERE sth.staff_id = ?
                  AND sth.status = 'SCHEDULED'
                  AND ts.depart_date >= CURDATE()
                GROUP BY sth.id
                ORDER BY ts.depart_date ASC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$staff_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Xóa phân công
     */
    public function removeAssignment($staff_tour_history_id)
    {
        try {
            // Lấy thông tin trước khi xóa
            $stmt = $this->pdo->prepare("SELECT staff_id, tour_schedule_id, role FROM staff_tour_history WHERE id = ?");
            $stmt->execute([$staff_tour_history_id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) return false;

            // Xóa record
            $stmt = $this->pdo->prepare("DELETE FROM staff_tour_history WHERE id = ?");
            $stmt->execute([$staff_tour_history_id]);

            // Clear guide_id trong tour_schedule
            $column = ($record['role'] === 'GUIDE') ? 'guide_id' : 'assistant_guide_id';
            $sql = "UPDATE tour_schedule SET $column = NULL WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$record['tour_schedule_id']]);

            return true;
        } catch (PDOException $e) {
            error_log("removeAssignment Error: " . $e->getMessage());
            return false;
        }
    }
}