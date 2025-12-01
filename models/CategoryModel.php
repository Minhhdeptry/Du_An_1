<?php
class CategoryModel
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

    public function getAll()
    {
        $sql = "SELECT * FROM tour_category ORDER BY id DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tour_category WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function store($data)
    {
        $sql = "INSERT INTO tour_category (code, name, note, is_active)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['code'],
            $data['name'],
            $data['note'],
            $data['is_active']
        ]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE tour_category 
                SET code=?, name=?, note=?, is_active=?
                WHERE id=?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['code'],
            $data['name'],
            $data['note'],
            $data['is_active'],
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tour_category WHERE id=?");
        return $stmt->execute([$id]);
    }
}
