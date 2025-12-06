<?php
class TourModel
{
    private $pdo;

    public function __construct()
    {
        require_once "./commons/function.php";
        $this->pdo = connectDB(); // bạn đã dùng connectDB thì gọi đúng nó
    }

    public function getAll()
    {
        $sql = "SELECT t.*, c.name AS category_name
            FROM tours t
            LEFT JOIN tour_category c ON t.category_id = c.id
            ORDER BY t.code ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function searchByKeywordWithStatus($keyword)
    {
        $sql = "SELECT t.*, c.name AS category_name
            FROM tours t
            LEFT JOIN tour_category c ON t.category_id = c.id
            WHERE t.title LIKE :kw OR t.code LIKE :kw
            ORDER BY t.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':kw' => "%$keyword%"]);
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // thêm display_status
        foreach ($tours as &$tour) {
            $stmt2 = $this->pdo->prepare("SELECT status FROM tour_schedule WHERE tour_id = ?");
            $stmt2->execute([$tour['id']]);
            $schedules = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $hasOpen = false;
            foreach ($schedules as $s) {
                if ($s['status'] === 'OPEN') {
                    $hasOpen = true;
                    break;
                }
            }

            $tour['display_status'] = $hasOpen ? 'Hiển thị' : 'Ẩn';
        }

        return $tours;
    }




    public function store($data)
    {
        $sql = "INSERT INTO tours(code, title, short_desc, full_desc, adult_price, child_price, duration_days, 
            category_id, policy, image_url, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data["code"],
            $data["title"],
            $data["short_desc"],
            $data["full_desc"],
            $data["adult_price"],   // thêm
            $data["child_price"],   // thêm
            $data["duration_days"],
            $data["category_id"],
            $data["policy"],
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
            code=?, title=?, short_desc=?, full_desc=?, adult_price=?, child_price=?, duration_days=?, 
            category_id=?, policy=?, image_url=?, is_active=? 
            WHERE id=?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data["code"],
            $data["title"],
            $data["short_desc"],
            $data["full_desc"],
            $data["adult_price"],
            $data["child_price"],
            $data["duration_days"],
            $data["category_id"],
            $data["policy"],
            $data["image_url"],
            $data["is_active"],
            $data["id"]
        ]);
    }

    // lấy tất cả tour kèm tên danh mục
    public function getAllWithCategoryStatus()
    {
        $sql = "SELECT t.*, c.name AS category_name
            FROM tours t
            LEFT JOIN tour_category c ON t.category_id = c.id
            ORDER BY t.code ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Duyệt từng tour để xác định trạng thái hiển thị dựa trên lịch
        foreach ($tours as &$tour) {
            $stmt2 = $this->pdo->prepare("SELECT status FROM tour_schedule WHERE tour_id = ?");
            $stmt2->execute([$tour['id']]);
            $schedules = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $hasOpen = false;
            foreach ($schedules as $s) {
                if ($s['status'] === 'OPEN') {
                    $hasOpen = true;
                    break;
                }
            }

            // Thêm cột ảo 'display_status' dựa trên lịch
            $tour['display_status'] = $hasOpen ? 'Hiển thị' : 'Ẩn';
        }

        return $tours;
    }


    // Dành cho client
    public function getAllAvailableTours()
    {
        $sql = "SELECT t.*, c.name AS category_name
            FROM tours t
            LEFT JOIN tour_category c ON t.category_id = c.id
            WHERE t.is_active = 1
              AND EXISTS (
                  SELECT 1 FROM tour_schedule ts
                  WHERE ts.tour_id = t.id AND ts.status = 'OPEN'
              )
            ORDER BY t.code ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // tìm tour theo id kèm tên danh mục
    public function findWithCategory($id)
    {
        $sql = "SELECT t.*, c.name AS category_name
            FROM tours t
            LEFT JOIN tour_category c ON t.category_id = c.id
            WHERE t.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tours WHERE id=?");
        return $stmt->execute([$id]);
    }
}
