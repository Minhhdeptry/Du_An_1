<?php
class ReportController
{
    private $model;

    public function __construct()
    {
        require_once "./models/admin/ReportModel.php";
        $this->model = new ReportModel();
    }

    public function index($act)
    {
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        // Lấy dữ liệu báo cáo
        $customerData = $this->model->getTotalCustomersByTour($year);
        $revenueData  = $this->model->getRevenueByTour($year);

        $currentAct = $act;
        $view = "./views/admin/Report/index.php";
        include "./views/layout/adminLayout.php";
    }
}
