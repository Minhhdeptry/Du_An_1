<?php
class StaffModel
{
    private $pdo;

    public function __construct()
    {
        require_once __DIR__ . "/../commons/function.php";
        $this->pdo = connectDB();
    }

    // Lấy danh sách nhân viên + thông tin user nếu cần
    public function getAll()
    {
        $sql = "SELECT s.*, u.full_name, u.email 
                FROM staffs s
                LEFT JOIN users u ON u.id = s.user_id
                ORDER BY s.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function store($data)
    {
        $sql = "INSERT INTO staffs(user_id, phone, id_number, qualification, status)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data["user_id"],
            $data["phone"],
            $data["id_number"],
            $data["qualification"],
            $data["status"]
        ]);
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM staffs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data)
    {
        $sql = "UPDATE staffs SET 
                user_id=?, phone=?, id_number=?, qualification=?, status=? 
                WHERE id=?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data["user_id"],
            $data["phone"],
            $data["id_number"],
            $data["qualification"],
            $data["status"],
            $data["id"]
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM staffs WHERE id=?");
        return $stmt->execute([$id]);
    }
}
