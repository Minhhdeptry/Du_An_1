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

    public function store($data)
    {
        $sql = "INSERT INTO tour_schedule 
                (tour_id, depart_date, return_date, seats_total, seats_available, price_adult, price_children , status, note)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['tour_id'],
            $data['depart_date'],
            $data['return_date'],
            $data['seats_total'],
            $data['seats_available'],
            $data['price_adult'],
            $data['price_children'],
            $data['status'],
            $data['note']
        ]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE tour_schedule SET
                tour_id=?, depart_date=?, return_date=?, seats_total=?, seats_available=?, 
                price_adult=?, price_children=?, status=?, note=?
                WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['tour_id'],
            $data['depart_date'],
            $data['return_date'],
            $data['seats_total'],
            $data['seats_available'],
            $data['price_adult'],
            $data['price_children'],
            $data['status'],
            $data['note'],
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tour_schedule WHERE id=?");
        $stmt->execute([$id]);
    }
}
