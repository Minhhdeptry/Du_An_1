<?php
class ItineraryModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    // Lấy tất cả lịch trình của 1 tour
    public function getByTour($tour_id)
    {
        $sql = "SELECT * FROM tour_itinerary 
                WHERE tour_id = ? 
                ORDER BY day_number ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tour_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy 1 ngày cụ thể
    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tour_itinerary WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Thêm mới 1 ngày
    public function store($data)
    {
        $sql = "INSERT INTO tour_itinerary 
                (tour_id, day_number, title, description, activities, accommodation, meals)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['tour_id'],
            $data['day_number'],
            $data['title'],
            $data['description'] ?? '',
            $data['activities'] ?? '',
            $data['accommodation'] ?? '',
            $data['meals'] ?? ''
        ]);
    }

    // Cập nhật
    public function update($id, $data)
    {
        $sql = "UPDATE tour_itinerary SET 
                day_number = ?, title = ?, description = ?, 
                activities = ?, accommodation = ?, meals = ?
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['day_number'],
            $data['title'],
            $data['description'] ?? '',
            $data['activities'] ?? '',
            $data['accommodation'] ?? '',
            $data['meals'] ?? '',
            $id
        ]);
    }

    // Xóa
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tour_itinerary WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Lấy số ngày của tour
    public function getTotalDays($tour_id)
    {
        $stmt = $this->pdo->prepare("SELECT MAX(day_number) as total FROM tour_itinerary WHERE tour_id = ?");
        $stmt->execute([$tour_id]);
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }
}