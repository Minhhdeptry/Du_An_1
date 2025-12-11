<?php
// ============================================
// FILE: models/admin/StaffTourHistoryModel.php
// ✅ FIX: CHỈ HIỂN THỊ TOUR ĐÃ HOÀN THÀNH
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
     * ✅ FIX HOÀN CHỈNH: CHỈ LẤY LỊCH SỬ TOUR ĐÃ HOÀN THÀNH
     * - Tour phải có return_date < hôm nay
     * - Chỉ đếm booking hợp lệ
     * - Chỉ đếm khách của booking hợp lệ
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
                    -- ✅ CHỈ ĐÉM BOOKING HỢP LỆ
                    COUNT(DISTINCT CASE 
                        WHEN b.status IN ('CONFIRMED', 'DEPOSIT_PAID', 'COMPLETED') 
                        THEN b.id 
                    END) AS total_bookings,
                    -- ✅ CHỈ ĐÉM KHÁCH CỦA BOOKING HỢP LỆ
                    SUM(CASE 
                        WHEN b.status IN ('CONFIRMED', 'DEPOSIT_PAID', 'COMPLETED') 
                        THEN (b.adults + b.children)
                        ELSE 0
                    END) AS total_customers
                FROM staff_tour_history sth
                JOIN tour_schedule ts ON ts.id = sth.tour_schedule_id
                JOIN tours t ON t.id = ts.tour_id
                LEFT JOIN bookings b ON b.tour_schedule_id = ts.id
                WHERE sth.staff_id = ?
                  AND ts.return_date < CURDATE()  -- ✅ CHỈ LẤY TOUR ĐÃ KẾT THÚC
                  AND sth.status = 'COMPLETED'     -- ✅ VÀ ĐÃ ĐƯỢC ĐÁNH DẤU HOÀN THÀNH
                GROUP BY sth.id
                ORDER BY ts.depart_date DESC
                LIMIT ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$staff_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ✅ ĐÃ ĐÚNG: Lấy dữ liệu hiệu suất (CHỈ TOUR ĐÃ HOÀN THÀNH)
     */
    public function getPerformanceData($staff_id)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT sth.tour_schedule_id) AS total_tours,
                    -- ✅ CHỈ ĐÉM TOUR ĐÃ HOÀN THÀNH
                    COUNT(DISTINCT CASE 
                        WHEN sth.status = 'COMPLETED' 
                        AND ts.return_date < CURDATE()
                        THEN sth.tour_schedule_id 
                    END) AS completed_tours,
                    AVG(sth.performance_rating) AS avg_performance,
                    -- ✅ CHỈ ĐÉM KHÁCH CỦA TOUR ĐÃ HOÀN THÀNH
                    SUM(CASE 
                        WHEN sth.status = 'COMPLETED' 
                        AND ts.return_date < CURDATE()
                        AND b.status IN ('CONFIRMED', 'DEPOSIT_PAID', 'COMPLETED')
                        THEN (b.adults + b.children)
                        ELSE 0
                    END) AS total_customers_served,
                    -- ✅ CHỈ ĐÉM BOOKING HỢP LỆ CỦA TOUR ĐÃ HOÀN THÀNH
                    COUNT(DISTINCT CASE 
                        WHEN sth.status = 'COMPLETED' 
                        AND ts.return_date < CURDATE()
                        AND b.status IN ('CONFIRMED', 'DEPOSIT_PAID', 'COMPLETED')
                        THEN b.id 
                    END) AS total_bookings
                FROM staff_tour_history sth
                JOIN tour_schedule ts ON ts.id = sth.tour_schedule_id
                LEFT JOIN bookings b ON b.tour_schedule_id = ts.id
                WHERE sth.staff_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$staff_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * ✅ CẬP NHẬT: Tự động chuyển trạng thái SCHEDULED → COMPLETED sau khi tour kết thúc
     */
    public function autoCompleteFinishedTours()
    {
        $sql = "UPDATE staff_tour_history sth
                JOIN tour_schedule ts ON ts.id = sth.tour_schedule_id
                SET sth.status = 'COMPLETED'
                WHERE sth.status = 'SCHEDULED'
                  AND ts.return_date < CURDATE()";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
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
     * ✅ Lấy tour sắp tới của HDV (GIỮ NGUYÊN - ĐÚNG RỒI)
     */
    public function getUpcomingTours($staff_id, $limit = 5)
    {
        $sql = "SELECT 
                    sth.*,
                    t.code AS tour_code,
                    t.title AS tour_name,
                    ts.depart_date,
                    ts.return_date,
                    COUNT(DISTINCT CASE 
                        WHEN b.status IN ('CONFIRMED', 'DEPOSIT_PAID', 'COMPLETED') 
                        THEN b.id 
                    END) AS total_bookings
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
?>