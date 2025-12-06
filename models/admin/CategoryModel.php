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

    // Lấy tất cả danh mục, có tìm kiếm, đếm tour
    public function getAll($keyword = '')
    {
        $sql = "SELECT tc.*, COUNT(t.id) AS tour_count
                FROM tour_category tc
                LEFT JOIN tours t ON t.category_id = tc.id
                WHERE tc.name LIKE :keyword OR tc.code LIKE :keyword
                GROUP BY tc.id
                ORDER BY tc.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['keyword' => "%$keyword%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tour_category WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function codeExists($code, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) FROM tour_category WHERE code=?";
        $params = [$code];

        if ($excludeId) {
            $sql .= " AND id<>?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function store($data)
    {
        $sql = "INSERT INTO tour_category (code, name, note, is_active)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['code'],
            $data['name'],
            $data['note'] ?? '',
            $data['is_active'] ?? 1
        ]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE tour_category SET code=?, name=?, note=?, is_active=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['code'],
            $data['name'],
            $data['note'] ?? '',
            $data['is_active'] ?? 1,
            $id
        ]);
    }

    public function canDelete($id)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tours WHERE category_id=?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() == 0;
    }

    public function delete($id)
    {
        if ($this->canDelete($id)) {
            $stmt = $this->pdo->prepare("DELETE FROM tour_category WHERE id=?");
            return $stmt->execute([$id]);
        }
        return false;
    }
}