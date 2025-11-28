<?php

class DashboardModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function countTours()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();
    }

    public function countBookings()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    }

    public function countGuides()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM staffs WHERE role = 'guide'")->fetchColumn();
    }

    public function revenueThisMonth()
    {
        return $this->pdo->query("
            SELECT SUM(amount) 
            FROM payments 
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
              AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ")->fetchColumn() ?? 0;
    }
}
