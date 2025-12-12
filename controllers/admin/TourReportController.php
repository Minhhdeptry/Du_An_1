<?php
// ===================================
// controllers/admin/TourReportController.php
// ===================================

require_once "./models/admin/TourReportModel.php";
require_once "./models/admin/TourScheduleModel.php";
require_once "./models/admin/BookingCheckInModel.php";

class TourReportController
{
    private $reportModel;
    private $scheduleModel;
    private $checkInModel;

    public function __construct()
    {
        $this->reportModel = new TourReportModel();
        $this->scheduleModel = new TourScheduleModel();
        $this->checkInModel = new BookingCheckInModel();
    }

    /**
     * ✅ DANH SÁCH TOUR CẦN LÀM BÁO CÁO
     */
    public function listPendingReports($act)
    {
        $tours = $this->scheduleModel->getCompletedToursNeedReport();
        
        $pageTitle = "Tour cần làm báo cáo";
        $currentAct = $act;
        $view = "./views/admin/TourReport/pending.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * ✅ FORM TẠO BÁO CÁO SAU TOUR
     */
    public function createReport($act)
    {
        $schedule_id = $_GET['schedule_id'] ?? null;
        
        if (!$schedule_id) {
            $_SESSION['error'] = "❌ Tour không hợp lệ!";
            header("Location: index.php?act=admin-tour-reports");
            exit;
        }

        $schedule = $this->scheduleModel->find($schedule_id);
        $checkInStats = $this->checkInModel->getCheckInStatsBySchedule($schedule_id);
        $existingReport = $this->reportModel->getBySchedule($schedule_id);

        $pageTitle = "Báo cáo Tour - " . ($schedule['tour_title'] ?? '');
        $currentAct = $act;
        $view = "./views/admin/TourReport/form.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * ✅ LƯU BÁO CÁO
     */
    public function saveReport()
    {
        $data = [
            'schedule_id' => $_POST['schedule_id'] ?? null,
            'actual_guests' => $_POST['actual_guests'] ?? 0,
            'incidents' => $_POST['incidents'] ?? '',
            'customer_feedback' => $_POST['customer_feedback'] ?? '',
            'guide_notes' => $_POST['guide_notes'] ?? '',
            'expenses_summary' => $_POST['expenses_summary'] ?? '',
            'overall_rating' => $_POST['overall_rating'] ?? 5,
            'created_by' => $_SESSION['user_id'] ?? null
        ];

        $result = $this->reportModel->save($data);

        if ($result['ok']) {
            $_SESSION['success'] = "✅ Lưu báo cáo thành công!";
            header("Location: index.php?act=admin-tour-report-view&id=" . $result['id']);
        } else {
            $_SESSION['error'] = "❌ " . implode(", ", $result['errors']);
            header("Location: index.php?act=admin-tour-report-create&schedule_id=" . $data['schedule_id']);
        }
        exit;
    }

    /**
     * ✅ XEM CHI TIẾT BÁO CÁO
     */
    public function viewReport($act)
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = "❌ Báo cáo không tồn tại!";
            header("Location: index.php?act=admin-tour-reports");
            exit;
        }

        $report = $this->reportModel->find($id);
        
        $pageTitle = "Chi tiết báo cáo Tour";
        $currentAct = $act;
        $view = "./views/admin/TourReport/view.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * ✅ DANH SÁCH TẤT CẢ BÁO CÁO
     */
    public function listAll($act)
    {
        $from_date = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $to_date = $_GET['to_date'] ?? date('Y-m-d');
        $guide_id = $_GET['guide_id'] ?? null;

        $reports = $this->reportModel->getAll($from_date, $to_date, $guide_id);

        $pageTitle = "Tất cả báo cáo Tour";
        $currentAct = $act;
        $view = "./views/admin/TourReport/list.php";
        include "./views/layout/adminLayout.php";
    }
}