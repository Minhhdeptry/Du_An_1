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
    public function getMonthSchedules($month, $year, $staff_id = null)
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

    public function assignForm($act)
    {
        $tour_schedule_id = $_GET['schedule_id'] ?? null;

        if (!$tour_schedule_id) {
            $_SESSION['error'] = "❌ Không tìm thấy lịch tour!";
            header("Location: index.php?act=admin-schedule");
            exit;
        }

        // Lấy thông tin schedule KÈM tour_title
        $schedule = $this->getScheduleWithTour($tour_schedule_id);

        if (!$schedule) {
            $_SESSION['error'] = "❌ Lịch tour không tồn tại!";
            header("Location: index.php?act=admin-schedule");
            exit;
        }

        // ✅ CHECK SỐ BOOKING TRƯỚC KHI CHO PHÉP PHÂN CÔNG
        $pdo = $this->staffModel->getConnection();
        $stmtBooking = $pdo->prepare("
        SELECT COUNT(*) as booking_count,
               GROUP_CONCAT(booking_code SEPARATOR ', ') as booking_codes
        FROM bookings 
        WHERE tour_schedule_id = ? 
        AND status NOT IN ('CANCELED')
    ");
        $stmtBooking->execute([$tour_schedule_id]);
        $bookingInfo = $stmtBooking->fetch(PDO::FETCH_ASSOC);
        $bookingCount = (int) ($bookingInfo['booking_count'] ?? 0);
        $bookingCodes = $bookingInfo['booking_codes'] ?? '';

        // ✅ NẾU CHƯA CÓ BOOKING VÀ KHÔNG CÓ FORCE FLAG → CHẶN + HIỂN thị WARNING
        if ($bookingCount === 0 && empty($_GET['force'])) {
            $_SESSION['error'] = '
            <div class="alert alert-warning">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle-fill"></i> 
                    CẢNH BÁO: Lịch tour chưa có booking
                </h5>
                <hr>
                <p class="mb-2">
                    <strong>Lịch tour:</strong> ' . htmlspecialchars($schedule['tour_title']) . '<br>
                    <strong>Ngày khởi hành:</strong> ' . date('d/m/Y', strtotime($schedule['depart_date'])) . '<br>
                    <strong>Số booking hiện tại:</strong> <span class="badge bg-danger">0</span>
                </p>
                <hr>
                <p class="mb-2"><strong>💡 Khuyến nghị:</strong></p>
                <ul class="mb-3">
                    <li>Nên đợi có booking trước khi phân công HDV</li>
                    <li>Tối ưu nguồn lực và thời gian của HDV</li>
                    <li>Tránh phân công rồi phải hủy/thay đổi</li>
                </ul>
                <div class="d-flex gap-2">
                    <a href="?act=admin-staff-assign-form&schedule_id=' . $tour_schedule_id . '&force=1" 
                       class="btn btn-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Vẫn muốn phân công (không khuyến nghị)
                    </a>
                    <a href="?act=admin-schedule" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> 
                        Quay lại danh sách lịch
                    </a>
                </div>
            </div>
        ';
            header("Location: index.php?act=admin-schedule");
            exit;
        }

        // ✅ NẾU CÓ FORCE=1 → GHI LOG CẢNH BÁO
        if ($bookingCount === 0 && !empty($_GET['force'])) {
            error_log("⚠️ [FORCE ASSIGN] Admin phân công HDV cho schedule #{$tour_schedule_id} dù chưa có booking");

            // Hiển thị thông báo cảnh báo trên form
            $_SESSION['warning'] = '
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill"></i> 
                <strong>Chú ý:</strong> Bạn đang phân công HDV cho lịch chưa có booking. 
                Hãy đảm bảo rằng tour này sẽ có khách đặt.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        ';
        }

        // ✅ NẾU CÓ BOOKING → HIỂN THỊ THÔNG TIN
        if ($bookingCount > 0) {
            $_SESSION['success'] = '
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill"></i> 
                <strong>Lịch này có ' . $bookingCount . ' booking:</strong> ' . htmlspecialchars($bookingCodes) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        ';
        }

        // ✅ Lấy danh sách HDV đã được phân công
        $assignedStaffIds = $this->getAssignedStaffIds($tour_schedule_id);

        // Kiểm tra đã đủ HDV chưa
        $allowReassign = $_GET['allow_reassign'] ?? false;

        if (count($assignedStaffIds) >= 2 && !$allowReassign) {
            $_SESSION['error'] = "⚠️ Tour này đã có đủ HDV! <br>" .
                "HDV chính: " . ($schedule['guide_name'] ?? 'Chưa có') . "<br>" .
                "HDV phụ: " . ($schedule['assistant_name'] ?? 'Chưa có') . "<br>" .
                '<a href="?act=admin-staff-assign-form&schedule_id=' . $tour_schedule_id . '&allow_reassign=1" class="btn btn-sm btn-warning mt-2">Thay đổi HDV</a>';
            header("Location: index.php?act=admin-schedule");
            exit;
        }

        // ✅ Lấy HDV rảnh
        $available_staffs = $this->getAvailableStaffs(
            $schedule['depart_date'],
            $schedule['return_date'],
            $tour_schedule_id,
            $assignedStaffIds
        );

        $pageTitle = "Phân công HDV - " . $schedule['tour_title'];
        $currentAct = $act;
        $view = "./views/admin/Staff/assign.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * ✅ Lấy danh sách ID HDV đã được phân công cho tour này
     */
    public function getAssignedStaffIds($tour_schedule_id)
    {
        $sql = "SELECT guide_id, assistant_guide_id 
                FROM tour_schedule 
                WHERE id = ?";

        $pdo = $this->staffModel->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tour_schedule_id]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

        $ids = [];
        if (!empty($schedule['guide_id'])) {
            $ids[] = $schedule['guide_id'];
        }
        if (!empty($schedule['assistant_guide_id'])) {
            $ids[] = $schedule['assistant_guide_id'];
        }

        return $ids;
    }

    /**
     * ✅ Lấy schedule kèm tour_title và tên HDV
     */
    public function getScheduleWithTour($schedule_id)
    {
        $sql = "SELECT ts.*, t.title AS tour_title, t.code AS tour_code,
                       u1.full_name AS guide_name, u2.full_name AS assistant_name
                FROM tour_schedule ts
                JOIN tours t ON t.id = ts.tour_id
                LEFT JOIN staffs s1 ON s1.id = ts.guide_id
                LEFT JOIN users u1 ON u1.id = s1.user_id
                LEFT JOIN staffs s2 ON s2.id = ts.assistant_guide_id
                LEFT JOIN users u2 ON u2.id = s2.user_id
                WHERE ts.id = ?";

        $pdo = $this->staffModel->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$schedule_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * ✅ LƯU PHÂN CÔNG HDV (CÓ VALIDATION)
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

        // ✅ VALIDATE 1: Phải chọn ít nhất 1 HDV
        if (empty($guide_id) && empty($assistant_guide_id)) {
            $_SESSION['error'] = "❌ Vui lòng chọn ít nhất 1 HDV!";
            header("Location: index.php?act=admin-staff-assign-form&schedule_id=" . $tour_schedule_id);
            exit;
        }

        // ✅ VALIDATE 2: HDV chính và HDV phụ KHÔNG được trùng nhau
        if ($guide_id && $assistant_guide_id && $guide_id == $assistant_guide_id) {
            $_SESSION['error'] = "❌ HDV chính và HDV phụ không được là cùng 1 người!";
            header("Location: index.php?act=admin-staff-assign-form&schedule_id=" . $tour_schedule_id);
            exit;
        }

        try {
            // ✅ Phân công HDV chính
            if ($guide_id) {
                $result = $this->historyModel->assignStaffToTour($guide_id, $tour_schedule_id, 'GUIDE');
                if (!$result['ok']) {
                    throw new Exception($result['error']);
                }
            }

            // ✅ Phân công HDV phụ
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
     * ✅ Lấy HDV rảnh (loại trừ HDV đang bận VÀ đã được phán công)
     */
    public function getAvailableStaffs($depart_date, $return_date, $current_schedule_id = null, $excludeStaffIds = [])
    {
        $params = [
            $current_schedule_id ?? 0,
            $depart_date,
            $return_date,
            $depart_date,
            $return_date,
            $depart_date,
            $return_date
        ];

        // ✅ Tạo placeholder cho exclude IDs
        $excludePlaceholders = '';
        if (!empty($excludeStaffIds)) {
            $excludePlaceholders = ' AND s.id NOT IN (' . implode(',', array_fill(0, count($excludeStaffIds), '?')) . ')';
            $params = array_merge($params, $excludeStaffIds);
        }

        $sql = "SELECT s.id, u.full_name, u.email, s.staff_type, s.rating,
               -- ✅ Kiểm tra xem HDV có TRÙNG LỊCH không
               (SELECT COUNT(*) 
                FROM tour_schedule ts2 
                WHERE (ts2.guide_id = s.id OR ts2.assistant_guide_id = s.id)
                  AND ts2.status IN ('OPEN', 'CLOSED')
                  AND ts2.id != COALESCE(?, 0)
                  AND (
                      -- Trùng lịch khi:
                      -- 1. Ngày bắt đầu tour mới nằm giữa tour cũ
                      (? BETWEEN ts2.depart_date AND ts2.return_date)
                      -- 2. Ngày kết thúc tour mới nằm giữa tour cũ
                      OR (? BETWEEN ts2.depart_date AND ts2.return_date)
                      -- 3. Tour mới bao trùm tour cũ
                      OR (ts2.depart_date BETWEEN ? AND ?)
                      OR (ts2.return_date BETWEEN ? AND ?)
                  )
               ) as conflict_count
            FROM staffs s
            JOIN users u ON u.id = s.user_id
            WHERE s.status = 'ACTIVE'
              AND u.role = 'HDV'
              {$excludePlaceholders}
            HAVING conflict_count = 0
            ORDER BY s.rating DESC, u.full_name ASC";

        $pdo = $this->staffModel->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ✅ Hủy phân công HDV (chính hoặc phụ) từ Schedule
     */
    public function removeGuide()
    {
        $schedule_id = $_GET['schedule_id'] ?? null;
        $type = $_GET['type'] ?? null; // 'guide' hoặc 'assistant'

        if (!$schedule_id || !$type) {
            $_SESSION['error'] = "❌ Thiếu thông tin!";
            header("Location: index.php?act=admin-schedule");
            exit;
        }

        try {
            $pdo = $this->staffModel->getConnection();
            $pdo->beginTransaction();

            // Xác định cột cần clear
            $column = ($type === 'guide') ? 'guide_id' : 'assistant_guide_id';
            $role = ($type === 'guide') ? 'GUIDE' : 'ASSISTANT';

            // 1. Clear guide_id/assistant_guide_id trong tour_schedule
            $stmt = $pdo->prepare("UPDATE tour_schedule SET $column = NULL WHERE id = ?");
            $stmt->execute([$schedule_id]);

            // 2. Xóa record trong staff_tour_history
            $stmt = $pdo->prepare("
                DELETE FROM staff_tour_history 
                WHERE tour_schedule_id = ? AND role = ?
            ");
            $stmt->execute([$schedule_id, $role]);

            $pdo->commit();

            $_SESSION['success'] = "✅ Đã hủy phân công HDV thành công!";
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
        }

        header("Location: index.php?act=admin-schedule");
        exit;
    }

    /**
     * Xóa phân công từ history
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
     * Lấy dữ liệu hiệu suất HDV
     */
    public function getPerformanceData($staff_id)
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