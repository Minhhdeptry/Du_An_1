<?php
class TourScheduleModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/env.php";
        require_once "./commons/function.php";

        global $pdo;
        if (function_exists("connectDB")) {
            $pdo = connectDB();
        }

        $this->pdo = $pdo;
    }

    // Lấy tất cả lịch, kèm tour + danh mục
    public function getAll()
    {
        $sql = "SELECT ts.*, t.title AS tour_title, t.code AS tour_code, c.name AS category_name
                FROM tour_schedule ts
                JOIN tours t ON ts.tour_id = t.id
                LEFT JOIN tour_category c ON t.category_id = c.id
                ORDER BY ts.id DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tìm kiếm lịch theo tour, mã tour, ngày đi/về, danh mục
    public function searchByKeyword($keyword)
    {
        $sql = "SELECT ts.*, t.title AS tour_title, t.code AS tour_code, c.name AS category_name
                FROM tour_schedule ts
                JOIN tours t ON ts.tour_id = t.id
                LEFT JOIN tour_category c ON t.category_id = c.id
                WHERE t.title LIKE :kw
                   OR t.code LIKE :kw
                   OR ts.depart_date LIKE :kw
                   OR ts.return_date LIKE :kw
                   OR c.name LIKE :kw
                ORDER BY ts.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':kw' => "%$keyword%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tour_schedule WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo lịch mới
    public function store($data)
    {
        // Khi tạo, seats_available = seats_total (chưa có booking nào)
        $sql = "INSERT INTO tour_schedule 
                (tour_id, depart_date, return_date, seats_total, seats_available, price_adult, price_children, status, note)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['tour_id'],
            $data['depart_date'],
            $data['return_date'],
            $data['seats_total'],
            $data['seats_total'], // tự động đặt bằng tổng ghế
            $data['price_adult'],
            $data['price_children'],
            $data['status'],
            $data['note']
        ]);
    }

    // Cập nhật lịch
    public function update($id, $data)
    {
        $sql = "UPDATE tour_schedule SET
                tour_id=?, depart_date=?, return_date=?, seats_total=?, 
                price_adult=?, price_children=?, status=?, note=?
                WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['tour_id'],
            $data['depart_date'],
            $data['return_date'],
            $data['seats_total'],
            $data['price_adult'],
            $data['price_children'],
            $data['status'],
            $data['note'],
            $id
        ]);

        // Cập nhật seats_available dựa trên booking hiện tại
        $this->updateSeats($id);
    }

    // Xóa lịch
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tour_schedule WHERE id=?");
        $stmt->execute([$id]);
    }

    // Cập nhật seats_available dựa trên booking hiện tại
    public function updateSeats($schedule_id)
    {
        // Tổng số người đã đặt (status còn hiệu lực)
        $stmt = $this->pdo->prepare("
            SELECT SUM(adults + children) AS booked
            FROM bookings
            WHERE tour_schedule_id = ? AND status IN ('PENDING','CONFIRMED','PAID','COMPLETED')
        ");
        $stmt->execute([$schedule_id]);
        $booked = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['booked'] ?? 0);

        // Lấy tổng số ghế
        $stmt2 = $this->pdo->prepare("SELECT seats_total FROM tour_schedule WHERE id = ?");
        $stmt2->execute([$schedule_id]);
        $seats_total = (int) $stmt2->fetch(PDO::FETCH_ASSOC)['seats_total'];

        // Cập nhật seats_available = seats_total - booked
        $stmt3 = $this->pdo->prepare("UPDATE tour_schedule SET seats_available = ? WHERE id = ?");
        $stmt3->execute([$seats_total - $booked, $schedule_id]);
    }

    // TourScheduleModel.php
    public function hasBooking($schedule_id)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE tour_schedule_id=?");
        $stmt->execute([$schedule_id]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
        return $count > 0;
    }

}
