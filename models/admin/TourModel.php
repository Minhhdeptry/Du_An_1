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
            WHERE t.title LIKE ?
               OR t.code LIKE ?
            ORDER BY t.id DESC";

        $stmt = $this->pdo->prepare($sql);

        $kw = "%{$keyword}%";
        $stmt->execute([$kw, $kw]);

        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- Thêm display_status ---
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

    public function getTourStats($tour_id)
    {
        try {
            // Tổng số lịch khởi hành
            $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total 
            FROM tour_schedule 
            WHERE tour_id = ?
        ");
            $stmt->execute([$tour_id]);
            $total_schedules = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Tổng booking
            $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT b.id) as total
            FROM bookings b
            JOIN tour_schedule ts ON b.tour_schedule_id = ts.id
            WHERE ts.tour_id = ?
              AND b.status NOT IN ('CANCELED')
        ");
            $stmt->execute([$tour_id]);
            $total_bookings = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Tổng khách
            $stmt = $this->pdo->prepare("
            SELECT SUM(b.adults + b.children) as total
            FROM bookings b
            JOIN tour_schedule ts ON b.tour_schedule_id = ts.id
            WHERE ts.tour_id = ?
              AND b.status NOT IN ('CANCELED')
        ");
            $stmt->execute([$tour_id]);
            $total_customers = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Tổng doanh thu
            $stmt = $this->pdo->prepare("
            SELECT SUM(b.total_amount) as total
            FROM bookings b
            JOIN tour_schedule ts ON b.tour_schedule_id = ts.id
            WHERE ts.tour_id = ?
              AND b.status IN ('DEPOSIT_PAID', 'COMPLETED')
        ");
            $stmt->execute([$tour_id]);
            $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            return [
                'total_schedules' => (int) $total_schedules,
                'total_bookings' => (int) $total_bookings,
                'total_customers' => (int) $total_customers,
                'total_revenue' => (float) $total_revenue,
            ];

        } catch (PDOException $e) {
            error_log("getTourStats Error: " . $e->getMessage());
            return [
                'total_schedules' => 0,
                'total_bookings' => 0,
                'total_customers' => 0,
                'total_revenue' => 0,
            ];
        }
    }


    public function store()
    {
        $imageName = null;

        if (!empty($_FILES["image_file"]["name"])) {
            $uploadDir = "assets/images/";
            $imageName = time() . "_" . basename($_FILES["image_file"]["name"]);
            move_uploaded_file($_FILES["image_file"]["tmp_name"], $uploadDir . $imageName);
        }

        $sql = "INSERT INTO tours 
        (code, title, short_desc, full_desc, adult_price, child_price, duration_days, 
         category_id, policy, image_url, is_active, default_seats)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $_POST["code"],
            $_POST["title"],
            $_POST["short_desc"],
            $_POST["full_desc"],
            $_POST["adult_price"],
            $_POST["child_price"],
            $_POST["duration_days"],
            $_POST["category_id"],
            $_POST["policy"],
            $imageName,
            $_POST["is_active"],
            $_POST["default_seats"] ?? 30  // ✅ Thêm dòng này
        ]);

        header("Location: index.php?act=admin-tour");
        exit;
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
        code=?, title=?, short_desc=?, full_desc=?, 
        adult_price=?, child_price=?, duration_days=?, 
        category_id=?, policy=?, image_url=?, is_active=?, default_seats=?
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
            $data["default_seats"] ?? 30,  // ✅ Thêm dòng này
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

    public function filterByCategory($category_id)
    {
        $sql = "SELECT t.*, c.name AS category_name
            FROM tours t
            LEFT JOIN tour_category c ON t.category_id = c.id
            WHERE t.category_id = ?
            ORDER BY t.code ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$category_id]);
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Thêm display_status
        foreach ($tours as &$tour) {
            $stmt2 = $this->pdo->prepare("SELECT status FROM tour_schedule WHERE tour_id = ?");
            $stmt2->execute([$tour['id']]);
            $schedules = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $tour['display_status'] = 'Ẩn';
            foreach ($schedules as $s) {
                if ($s['status'] === 'OPEN') {
                    $tour['display_status'] = 'Hiển thị';
                    break;
                }
            }
        }

        return $tours;
    }


    public function searchByKeywordAndCategory($keyword, $category_id)
    {
        $sql = "SELECT t.*, c.name AS category_name
            FROM tours t
            LEFT JOIN tour_category c ON t.category_id = c.id
            WHERE (t.title LIKE ? OR t.code LIKE ?)
              AND t.category_id = ?
            ORDER BY t.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $kw = "%{$keyword}%";
        $stmt->execute([$kw, $kw, $category_id]);
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- Thêm display_status ---
        foreach ($tours as &$tour) {
            $stmt2 = $this->pdo->prepare("SELECT status FROM tour_schedule WHERE tour_id = ?");
            $stmt2->execute([$tour['id']]);
            $schedules = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $tour['display_status'] = 'Ẩn';
            foreach ($schedules as $s) {
                if ($s['status'] === 'OPEN') {
                    $tour['display_status'] = 'Hiển thị';
                    break;
                }
            }
        }

        return $tours;
    }
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM tours WHERE id = ?");
        return $stmt->execute([$id]);
    }

}
