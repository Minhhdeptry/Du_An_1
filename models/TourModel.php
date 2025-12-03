<?php
class TourModel
{
    private $pdo;

    public function __construct()
    {
        require_once __DIR__ . "/../commons/function.php";
        $this->pdo = connectDB(); // bạn đã dùng connectDB thì gọi đúng nó
    }

    public function getAll()
    {
        $sql = "SELECT * FROM tours ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchByKeyword($keyword)
    {
        $sql = "SELECT * FROM tours 
            WHERE title LIKE :kw OR code LIKE :kw
            ORDER BY id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':kw' => "%$keyword%"
        ]);

        return $stmt->fetchAll();
    }



    public function store($data)
    {
        $sql = "INSERT INTO tours(code, title, short_desc, full_desc, base_price, duration_days, 
                category_id, policy, supplier, image_url, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data["code"],
            $data["title"],
            $data["short_desc"],
            $data["full_desc"],
            $data["base_price"],
            $data["duration_days"],
            $data["category_id"],
            $data["policy"],
            $data["supplier"],
            $data["image_url"],
            $data["is_active"]
        ]);
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data)
    {
        $sql = "UPDATE tours SET 
                code=?, title=?, short_desc=?, full_desc=?, base_price=?, duration_days=?, 
                category_id=?, policy=?, supplier=?, image_url=?, is_active=? 
                WHERE id=?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data["code"],
            $data["title"],
            $data["short_desc"],
            $data["full_desc"],
            $data["base_price"],
            $data["duration_days"],
            $data["category_id"],
            $data["policy"],
            $data["supplier"],
            $data["image_url"],
            $data["is_active"],
            $data["id"]
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tours WHERE id=?");
        return $stmt->execute([$id]);
    }
}
