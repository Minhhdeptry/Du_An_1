<?php
class TourReportController
{
    private $model;

    public function __construct()
    {
        require_once "./models/admin/TourReportModel.php";
        $this->model = new TourReportModel();
    }

    /**
     * Trang tổng quan báo cáo năm
     */
    public function index($act)
    {
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        // Lấy dữ liệu báo cáo tổng hợp theo tour
        $customerData     = $this->model->getTotalCustomersByTour($year);
        $revenueData      = $this->model->getRevenueByTour($year);

        // ⭐ LẤY DỮ LIỆU KHÁCH THEO THÁNG
        $customerByMonth  = $this->model->getTotalCustomersByMonth($year);

        // ⭐ LẤY DỮ LIỆU DOANH THU THEO THÁNG
        $revenueByMonth   = $this->model->getRevenueByMonth($year);

        $currentAct = $act;
        $view = "./views/admin/Report/index.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Báo cáo theo tháng
     */
    public function monthly($act)
    {
        $month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
        $year  = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        $from_date = "$year-$month-01";
        $to_date   = date("Y-m-t", strtotime($from_date));

        $reportList = $this->model->getAll($from_date, $to_date);

        $currentAct = $act;
        $view = "./views/admin/Report/monthly.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Danh sách và nhập báo cáo cho từng schedule
     */
    public function schedule($act)
    {
        if (!isset($_GET['id'])) {
            die("Thiếu schedule_id");
        }

        $schedule_id = intval($_GET['id']);

        // Lấy báo cáo (nếu có)
        $report = $this->model->getBySchedule($schedule_id);

        $currentAct = $act;
        $view = "./views/admin/Report/schedule.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Lưu báo cáo từ form
     */
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Invalid request");
        }

        $data = [
            'schedule_id'       => $_POST['schedule_id'] ?? null,
            'actual_guests'     => $_POST['actual_guests'] ?? 0,
            'incidents'         => $_POST['incidents'] ?? null,
            'customer_feedback' => $_POST['customer_feedback'] ?? null,
            'guide_notes'       => $_POST['guide_notes'] ?? null,
            'expenses_summary'  => $_POST['expenses_summary'] ?? null,
            'overall_rating'    => $_POST['overall_rating'] ?? null,
            'created_by'        => $_SESSION['user']['id'] ?? 1
        ];

        $result = $this->model->save($data);

        if ($result['ok']) {
            header("Location: index.php?module=report&act=schedule&id=" . $data['schedule_id']);
            exit;
        } else {
            echo "<pre>";
            print_r($result['errors']);
            echo "</pre>";
        }
    }

    /**
     * Xem chi tiết báo cáo
     */
    public function detail($act)
    {
        if (!isset($_GET['id'])) {
            die("Thiếu id báo cáo");
        }

        $id = intval($_GET['id']);
        $report = $this->model->find($id);

        if (!$report) {
            die("Không tìm thấy báo cáo");
        }

        $currentAct = $act;
        $view = "./views/admin/Report/detail.php";
        include "./views/layout/adminLayout.php";
    }
}
