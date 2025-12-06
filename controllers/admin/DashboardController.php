<?php
require_once "./models/admin/DashboardModel.php";

class DashboardController {
    private $model;

    public function __construct() {
        $this->model = new DashboardModel(); // tự connectDB trong model
    }

    public function index($act) {
        // Section 1
        $kpi = [
            'totalTours' => $this->model->getTotalTours(),
            'totalBookings' => $this->model->getTotalBookings(),
            'totalCustomers' => $this->model->getTotalCustomers(),
            'totalGuides' => $this->model->getTotalGuides(),
            'revenueThisMonth' => $this->model->getRevenueThisMonth(),
            'toursToday' => $this->model->getToursToday()
        ];

        // Section 2
        $charts = [
            'revenue12Months' => $this->model->getRevenue12Months(),
            'bookingStatus' => $this->model->getBookingStatusCount()
        ];

        // Section 3
        $tables = [
            'latestBookings' => $this->model->getLatestBookings(),
            'upcomingTours' => $this->model->getUpcomingTours(),
            'latestCustomers' => $this->model->getLatestCustomers()
        ];

        // Section 4
        $logs = $this->model->getRecentLogs();

        // Load view
        $currentAct = $act;
        $pageTitle = "Dashboard";
        $view = "./views/admin/Dashboard/index.php";
        include "./views/layout/adminLayout.php";
    }
}
