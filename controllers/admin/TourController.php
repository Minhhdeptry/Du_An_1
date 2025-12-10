<?php
class TourController
{
    private $model;
    private $pdo;

    public function __construct()
    {
        require_once "./commons/env.php";
        require_once "./commons/function.php";

        $this->pdo = connectDB();

        require_once "./models/admin/TourModel.php";
        $this->model = new TourModel();
    }

    public function index($act)
    {
        $pageTitle = "Quản lý Tour";
        $currentAct = $act;

        $keyword = trim($_GET['keyword'] ?? '');
        $tours = $keyword !== ''
            ? $this->model->searchByKeywordWithStatus($keyword)
            : $this->model->getAllWithCategoryStatus(); // join category để hiển thị tên danh mục

        $view = "./views/admin/Tours/index.php";
        include "./views/layout/adminLayout.php";
    }

    public function create($act)
    {
        $categories = $this->pdo->query("SELECT id, name FROM tour_category")->fetchAll();
        $pageTitle = "Thêm Tour";
        $currentAct = $act;

        $view = "./views/admin/Tours/create.php";
        include "./views/layout/adminLayout.php";
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

    public function edit($act)
    {
        $categories = $this->pdo->query("SELECT id, name FROM tour_category")->fetchAll();
        $id = $_GET["id"];
        $tour = $this->model->findWithCategory($id);

        $pageTitle = "Sửa Tour";
        $currentAct = $act;

        $view = "./views/admin/Tours/edit.php";
        include "./views/layout/adminLayout.php";
    }

    public function update()
    {
        $id = $_POST["id"];
        $imageName = $_POST["old_image"];

        if (!empty($_FILES["image_file"]["name"])) {
            $uploadDir = "assets/images/";
            $imageName = time() . "_" . basename($_FILES["image_file"]["name"]);
            move_uploaded_file($_FILES["image_file"]["tmp_name"], $uploadDir . $imageName);
        }

        $sql = "UPDATE tours SET 
            code=?, title=?, short_desc=?, full_desc=?, 
            adult_price=?, child_price=?, duration_days=?, 
            category_id=?, policy=?, image_url=?, is_active=?
            WHERE id=?";
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
            $id
        ]);

        header("Location: index.php?act=admin-tour");
        exit;
    }

    public function delete()
    {
        $id = $_GET["id"];
        $this->model->delete($id);

        header("Location: index.php?act=admin-tour");
        exit;
    }

    // Thêm method này vào controllers/admin/TourController.php

    // Thêm method này vào controllers/admin/TourController.php

    public function detail($act)
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header("Location: index.php?act=admin-tour");
            exit;
        }

        // Lấy thông tin tour
        $tour = $this->model->findWithCategory($id);

        if (!$tour) {
            $_SESSION['error'] = "❌ Tour không tồn tại!";
            header("Location: index.php?act=admin-tour");
            exit;
        }

        // ✅ Kiểm tra có lịch mở không
        $stmt = $this->pdo->prepare("SELECT status FROM tour_schedule WHERE tour_id = ?");
        $stmt->execute([$id]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hasOpen = false;
        foreach ($schedules as $s) {
            if ($s['status'] === 'OPEN') {
                $hasOpen = true;
                break;
            }
        }

        $tour['display_status'] = $hasOpen ? 'Hiển thị' : 'Ẩn';

        // Lấy lịch trình
        require_once "./models/admin/ItineraryModel.php";
        $itineraryModel = new ItineraryModel();
        $itineraries = $itineraryModel->getByTour($id);

        // Lấy thống kê
        $stats = $this->model->getTourStats($id);

        $pageTitle = "Chi tiết: " . $tour['title'];
        $currentAct = $act;
        $view = "./views/admin/Tours/detail.php";
        include "./views/layout/adminLayout.php";
    }
}
