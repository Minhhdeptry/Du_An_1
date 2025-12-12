<?php
class TourReportModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    /**
     * Tổng khách theo từng tour mỗi tháng
     * Trả về mảng: ['Tour A' => [1=>10,2=>20,...,12=>0], ...]
     */
    public function getTotalCustomersByTour($year = null)
    {
        // 1. Lấy danh sách tất cả tour
        $stmt = $this->pdo->query("SELECT id, title FROM tours ORDER BY title ASC");
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Khởi tạo mảng 12 tháng = 0 cho mỗi tour
        $result = [];
        foreach ($tours as $tour) {
            $result[$tour['title']] = array_fill(1, 12, 0);
        }

        // 2. Lấy dữ liệu bookings đã xác nhận
        $params = [];
        $sql = "SELECT 
                    t.title AS tour_name,
                    MONTH(b.created_at) AS month,
                    SUM(b.adults + b.children) AS total_customers
                FROM bookings b
                JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
                JOIN tours t ON t.id = ts.tour_id
                WHERE b.status IN ('CONFIRMED','PAID','COMPLETED')";

        if ($year) {
            $sql .= " AND YEAR(b.created_at) = ?";
            $params[] = $year;
        }

        $sql .= " GROUP BY t.title, month ORDER BY t.title, month";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Ghi dữ liệu vào mảng tour
        foreach ($rows as $row) {
            $tour = $row['tour_name'];
            $month = intval($row['month']);
            $total = intval($row['total_customers']);
            $result[$tour][$month] = $total;
        }

        // Sắp xếp tháng tăng dần cho mỗi tour
        foreach ($result as $tour => $months) {
            ksort($result[$tour]);
        }

        return $result;
    }

    /**
     * Doanh thu theo từng tour mỗi tháng
     * Trả về mảng: ['Tour A' => [1=>1000000,2=>2000000,...,12=>0], ...]
     */
    public function getRevenueByTour($year = null)
    {
        // 1. Lấy danh sách tất cả tour
        $stmt = $this->pdo->query("SELECT id, title FROM tours ORDER BY title ASC");
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($tours as $tour) {
            $result[$tour['title']] = array_fill(1, 12, 0);
        }

        // 2. Lấy dữ liệu doanh thu từ bookings đã thanh toán
        $params = [];
        $sql = "SELECT 
                    t.title AS tour_name,
                    MONTH(b.created_at) AS month,
                    SUM(b.total_amount) AS revenue
                FROM bookings b
                JOIN tour_schedule ts ON ts.id = b.tour_schedule_id
                JOIN tours t ON t.id = ts.tour_id
                WHERE b.status IN ('PAID','COMPLETED')";

        if ($year) {
            $sql .= " AND YEAR(b.created_at) = ?";
            $params[] = $year;
        }

        $sql .= " GROUP BY t.title, month ORDER BY t.title, month";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Ghi dữ liệu vào mảng tour
        foreach ($rows as $row) {
            $tour = $row['tour_name'];
            $month = intval($row['month']);
            $revenue = floatval($row['revenue']);
            $result[$tour][$month] = $revenue;
        }

        // Sắp xếp tháng tăng dần cho mỗi tour
        foreach ($result as $tour => $months) {
            ksort($result[$tour]);
        }

        return $result;
    }
}
?>