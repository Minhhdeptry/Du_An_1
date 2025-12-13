<?php
class StaffRatingModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB();
    }

    /**
     * Lấy đánh giá của HDV
     */
    public function getStaffRatings($staff_id, $limit = 20)
    {
        $sql = "SELECT sr.*, u.full_name AS customer_name, b.booking_code
                FROM staff_ratings sr
                JOIN bookings b ON b.id = sr.booking_id
                LEFT JOIN users u ON u.id = sr.user_id
                WHERE sr.staff_id = ?
                ORDER BY sr.created_at DESC
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$staff_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm đánh giá
     */
    public function addRating($data)
    {
        $sql = "INSERT INTO staff_ratings 
                (staff_id, booking_id, user_id, rating, comment,
                 criteria_knowledge, criteria_communication, criteria_attitude, criteria_punctuality)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['staff_id'],
            $data['booking_id'],
            $data['user_id'] ?? null,
            $data['rating'],
            $data['comment'] ?? null,
            $data['criteria_knowledge'] ?? null,
            $data['criteria_communication'] ?? null,
            $data['criteria_attitude'] ?? null,
            $data['criteria_punctuality'] ?? null
        ]);
    }

    /**
     * Thống kê đánh giá theo tiêu chí
     */
    public function getRatingStatistics($staff_id)
    {
        $sql = "SELECT 
                    COUNT(*) AS total_ratings,
                    AVG(rating) AS avg_rating,
                    AVG(criteria_knowledge) AS avg_knowledge,
                    AVG(criteria_communication) AS avg_communication,
                    AVG(criteria_attitude) AS avg_attitude,
                    AVG(criteria_punctuality) AS avg_punctuality,
                    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) AS positive_ratings,
                    SUM(CASE WHEN rating < 3 THEN 1 ELSE 0 END) AS negative_ratings
                FROM staff_ratings
                WHERE staff_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$staff_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>