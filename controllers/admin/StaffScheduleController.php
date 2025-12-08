<?php
// ============================================
// FILE: controllers/admin/StaffScheduleController.php
// ============================================

require_once "./models/admin/StaffModel.php";
require_once "./models/admin/StaffTourHistoryModel.php";
require_once "./models/admin/TourScheduleModel.php";

class StaffScheduleController
{
    private $staffModel;
    private $historyModel;
    private $scheduleModel;

    public function __construct()
    {
        $this->staffModel = new StaffModel();
        $this->historyModel = new StaffTourHistoryModel();
        $this->scheduleModel = new TourScheduleModel();
    }

    /**
     * Lịch làm việc Calendar View
     */
    public function calendar($act)
    {
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        $staff_id = $_GET['staff_id'] ?? null;

        // Lấy danh sách HDV
        $staffs = $this->staffModel->getAll();

        // Lấy lịch của tháng hiện tại
        $schedules = $this->getMonthSchedules($month, $year, $staff_id);

        $pageTitle = "Lịch làm việc HDV";
        $currentAct = $act;
        $view = "./views/admin/Staff/calendar.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Lấy lịch theo tháng
     */
    private function getMonthSchedules($month, $year, $staff_id = null)
    {
        $sql = "SELECT 
                    ts.id,
                    ts.depart_date,
                    ts.return_date,
                    t.code AS tour_code,
                    t.title AS tour_name,
                    ts.status,
                    s.id AS staff_id,
                    u.full_name AS staff_name,
                    CASE 
                        WHEN ts.guide_id = s.id THEN 'GUIDE'
                        WHEN ts.assistant_guide_id = s.id THEN 'ASSISTANT'
                    END AS role
                FROM tour_schedule ts
                JOIN tours t ON t.id = ts.tour_id
                LEFT JOIN staffs s ON (s.id = ts.guide_id OR s.id = ts.assistant_guide_id)
                LEFT JOIN users u ON u.id = s.user_id
                WHERE YEAR(ts.depart_date) = ?
                  AND MONTH(ts.depart_date) = ?";

        $params = [$year, $month];

        if ($staff_id) {
            $sql .= " AND s.id = ?";
            $params[] = $staff_id;
        }

        $sql .= " ORDER BY ts.depart_date ASC";

        $pdo = $this->staffModel->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Form phân công HDV cho tour
     */
    /**
     * Form phân công HDV cho tour
     */
    public function assignForm($act)
    {
        $tour_schedule_id = $_GET['schedule_id'] ?? null;

        if (!$tour_schedule_id) {
            $_SESSION['error'] = "❌ Không tìm thấy lịch tour!";
            header("Location: index.php?act=admin-schedule");
            exit;
        }

        // Lấy thông tin tour
        $schedule = $this->scheduleModel->find($tour_schedule_id);

        if (!$schedule) {
            $_SESSION['error'] = "❌ Lịch tour không tồn tại!";
            header("Location: index.php?act=admin-schedule");
            exit;
        }

        // ✅ MỚI: Kiểm tra tour đã có HDV chưa
        $hasGuide = !empty($schedule['guide_id']);
        $hasAssistant = !empty($schedule['assistant_guide_id']);

        // ✅ MỚI: Nếu đã đủ HDV thì chặn (trừ khi muốn thay thế)
        $allowReassign = $_GET['allow_reassign'] ?? false;

        if (($hasGuide && $hasAssistant) && !$allowReassign) {
            $_SESSION['error'] = "⚠️ Tour này đã có đủ HDV! <br>" .
                "HDV chính: " . ($schedule['guide_name'] ?? 'N/A') . "<br>" .
                "HDV phụ: " . ($schedule['assistant_name'] ?? 'N/A') . "<br>" .
                '<a href="?act=admin-staff-assign-form&schedule_id=' . $tour_schedule_id . '&allow_reassign=1" class="btn btn-sm btn-warning mt-2">Thay đổi HDV</a>';
            header("Location: index.php?act=admin-schedule");
            exit;
        }

        // Lấy HDV đang rảnh (loại trừ HDV đang bận)
        $available_staffs = $this->getAvailableStaffs(
            $schedule['depart_date'],
            $schedule['return_date'],
            $tour_schedule_id // ✅ SỬA: Dùng $tour_schedule_id thay vì $schedule_id
        );

        $pageTitle = "Phân công HDV - " . $schedule['tour_title'];
        $currentAct = $act;
        $view = "./views/admin/Staff/assign.php";
        include "./views/layout/adminLayout.php";
    }
    /**
     * Lưu phân công
     */
    public function assignStore()
    {
        $tour_schedule_id = $_POST['tour_schedule_id'] ?? null;
        $guide_id = $_POST['guide_id'] ?? null;
        $assistant_guide_id = $_POST['assistant_guide_id'] ?? null;

        if (!$tour_schedule_id) {
            $_SESSION['error'] = "❌ Thiếu thông tin lịch tour!";
            header("Location: index.php?act=admin-schedule");
            exit;
        }

        try {
            // Phân công HDV chính
            if ($guide_id) {
                $result = $this->historyModel->assignStaffToTour($guide_id, $tour_schedule_id, 'GUIDE');
                if (!$result['ok']) {
                    throw new Exception($result['error']);
                }
            }

            // Phân công HDV phụ
            if ($assistant_guide_id) {
                $result = $this->historyModel->assignStaffToTour($assistant_guide_id, $tour_schedule_id, 'ASSISTANT');
                if (!$result['ok']) {
                    throw new Exception($result['error']);
                }
            }

            $_SESSION['success'] = "✅ Phân công HDV thành công!";
            header("Location: index.php?act=admin-schedule");
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
            header("Location: index.php?act=admin-staff-assign-form&schedule_id=" . $tour_schedule_id);
            exit;
        }
    }

    /**
     * Lấy HDV đang rảnh
     */
    /**
     * Lấy HDV đang rảnh (đã loại trừ HDV đang bận)
     */
    private function getAvailableStaffs($depart_date, $return_date, $current_schedule_id = null)
    {
        $sql = "SELECT s.id, u.full_name, u.email, s.staff_type, s.rating,
                   -- Kiểm tra xem HDV có lịch trùng không
                   (SELECT COUNT(*) 
                    FROM tour_schedule ts2 
                    WHERE (ts2.guide_id = s.id OR ts2.assistant_guide_id = s.id)
                      AND ts2.status IN ('OPEN', 'CLOSED')
                      AND ts2.id != COALESCE(?, 0)
                      AND (
                          (ts2.depart_date BETWEEN ? AND ?)
                          OR (ts2.return_date BETWEEN ? AND ?)
                          OR (? BETWEEN ts2.depart_date AND ts2.return_date)
                          OR (? BETWEEN ts2.depart_date AND ts2.return_date)
                      )
                   ) as conflict_count
            FROM staffs s
            JOIN users u ON u.id = s.user_id
            WHERE s.status = 'ACTIVE'
              AND u.role = 'HDV'
            HAVING conflict_count = 0  -- ✅ Chỉ lấy HDV không có lịch trùng
            ORDER BY s.rating DESC, u.full_name ASC";

        $pdo = $this->staffModel->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $current_schedule_id,
            $depart_date,
            $return_date,
            $depart_date,
            $return_date,
            $depart_date,
            $return_date
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Xóa phân công
     */
    public function removeAssignment()
    {
        $history_id = $_GET['history_id'] ?? null;

        if (!$history_id) {
            $_SESSION['error'] = "❌ Không tìm thấy phân công!";
            header("Location: index.php?act=admin-schedule");
            exit;
        }

        if ($this->historyModel->removeAssignment($history_id)) {
            $_SESSION['success'] = "✅ Đã hủy phân công HDV!";
        } else {
            $_SESSION['error'] = "❌ Không thể hủy phân công!";
        }

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?act=admin-schedule'));
        exit;
    }

    /**
     * Dashboard hiệu suất HDV
     */
    public function performance($act)
    {
        $staff_id = $_GET['staff_id'] ?? null;

        if (!$staff_id) {
            $_SESSION['error'] = "❌ Không tìm thấy HDV!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $staff = $this->staffModel->find($staff_id);
        if (!$staff) {
            $_SESSION['error'] = "❌ HDV không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        // Lấy dữ liệu thống kê
        $performance = $this->getPerformanceData($staff_id);
        $history = $this->historyModel->getStaffHistory($staff_id, 20);
        $upcoming = $this->historyModel->getUpcomingTours($staff_id, 5);

        $pageTitle = "Hiệu suất HDV: " . $staff['full_name'];
        $currentAct = $act;
        $view = "./views/admin/Staff/performance.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Lấy dữ liệu hiệu suất
     */
    private function getPerformanceData($staff_id)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT sth.tour_schedule_id) AS total_tours,
                    COUNT(DISTINCT CASE WHEN sth.status = 'COMPLETED' THEN sth.tour_schedule_id END) AS completed_tours,
                    AVG(sth.performance_rating) AS avg_performance,
                    SUM(b.adults + b.children) AS total_customers_served,
                    COUNT(DISTINCT b.id) AS total_bookings
                FROM staff_tour_history sth
                JOIN tour_schedule ts ON ts.id = sth.tour_schedule_id
                LEFT JOIN bookings b ON b.tour_schedule_id = ts.id
                WHERE sth.staff_id = ?";

        $pdo = $this->staffModel->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$staff_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check HDV availability (API)
     */
    public function checkAvailability()
    {
        header('Content-Type: application/json');

        $staff_id = $_GET['staff_id'] ?? null;
        $schedule_id = $_GET['schedule_id'] ?? null;

        if (!$staff_id || !$schedule_id) {
            echo json_encode(['available' => false, 'message' => 'Missing parameters']);
            exit;
        }

        $available = $this->historyModel->checkAvailability($staff_id, $schedule_id);

        echo json_encode([
            'available' => $available,
            'message' => $available ? 'HDV đang rảnh' : 'HDV đã có lịch trùng!'
        ]);
        exit;
    }
}
?>