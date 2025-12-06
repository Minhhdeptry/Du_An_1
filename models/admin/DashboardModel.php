<?php
class DashboardModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    // Section 1: KPI
    public function getTotalTours()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();
    }

    public function getTotalBookings()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    }

    public function getTotalCustomers()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
    }

    public function getTotalGuides()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM users WHERE role='HDV'")->fetchColumn();
    }

    public function getRevenueThisMonth()
    {
        $stmt = $this->pdo->prepare("
            SELECT SUM(amount) FROM payments 
            WHERE MONTH(paid_at) = MONTH(CURRENT_DATE()) 
              AND YEAR(paid_at) = YEAR(CURRENT_DATE()) 
              AND status='SUCCESS'
        ");
        $stmt->execute();
        return $stmt->fetchColumn() ?? 0;
    }

    public function getToursToday()
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM tour_schedule 
            WHERE depart_date = CURRENT_DATE()
        ");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Section 2: Biểu đồ
    public function getRevenue12Months()
    {
        $stmt = $this->pdo->query("
            SELECT MONTH(paid_at) AS month, SUM(amount) AS total 
            FROM payments 
            WHERE YEAR(paid_at) = YEAR(CURRENT_DATE()) AND status='SUCCESS'
            GROUP BY MONTH(paid_at)
        ");
        $data = array_fill(1, 12, 0);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[(int) $row['month']] = (float) $row['total'];
        }
        return $data;
    }

    public function getBookingStatusCount()
    {
        $stmt = $this->pdo->query("
            SELECT status, COUNT(*) AS total FROM bookings GROUP BY status
        ");
        $result = ['PENDING' => 0, 'CONFIRMED' => 0, 'PAID' => 0, 'CANCELED' => 0];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['status']] = (int) $row['total'];
        }
        return $result;
    }

    // Section 3: Mini tables
    public function getLatestBookings($limit = 5)
    {
        $stmt = $this->pdo->query("
            SELECT b.booking_code, u.full_name AS customer, b.status
            FROM bookings b
            JOIN users u ON u.id = b.user_id
            ORDER BY b.created_at DESC
            LIMIT $limit
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingTours($limit = 5)
    {
        $stmt = $this->pdo->query("
            SELECT t.title, ts.depart_date 
            FROM tour_schedule ts
            JOIN tours t ON t.id = ts.tour_id
            WHERE ts.depart_date >= CURRENT_DATE()
            ORDER BY ts.depart_date ASC
            LIMIT $limit
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestCustomers($limit = 5)
    {
        $stmt = $this->pdo->query("
            SELECT full_name, email 
            FROM users 
            WHERE role='customer'
            ORDER BY created_at DESC
            LIMIT $limit
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Section 4: Logs / Notifications
    public function getRecentLogs($limit = 5)
    {
        $limit = (int) $limit; // ép kiểu integer
        $sql = "
        SELECT tl.*, u.full_name AS author_name
        FROM tour_logs tl
        LEFT JOIN users u ON u.id = tl.author_id
        ORDER BY tl.created_at DESC
        LIMIT $limit
    ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
