<?php
class DashboardController
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/env.php";
        require_once "./commons/function.php";

        // Kết nối DB
        if (function_exists("connectDB")) {
            $this->pdo = connectDB();
        } else {
            global $pdo;
            $this->pdo = $pdo;
        }
    }

    public function index($act)
    {
        // ------- LẤY SỐ LIỆU DASHBOARD ---------
        
        // Tổng tour
        $totalTours = $this->pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();

        // Tổng booking
        $totalBookings = $this->pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

        // Tổng hướng dẫn viên (role = 'HDV')
        $totalHDV = $this->pdo
            ->query("SELECT COUNT(*) FROM users WHERE role = 'HDV'")
            ->fetchColumn();

        // Doanh thu tháng hiện tại
        $month = date("m");
        $year  = date("Y");

        $stmt = $this->pdo->prepare("
            SELECT SUM(amount) AS total
            FROM payments
            WHERE MONTH(paid_at) = ?
            AND YEAR(paid_at) = ?
            AND status = 'SUCCESS'
        ");

        $stmt->execute([$month, $year]);

        $totalRevenue = $stmt->fetchColumn() ?? 0;


        // --------------------------------------

        $currentAct = $act;
        $pageTitle = "Dashboard";
        $view = "./views/admin/Dashboard/index.php";
        include "./views/layout/adminLayout.php";
    }
}
