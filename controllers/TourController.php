<?php
class TourController
{
    private $model;

    public function __construct()
    {
        require_once "./commons/env.php";
        require_once "./commons/function.php";

        global $pdo;
        if (function_exists("connectDB")) {
            $pdo = connectDB();
        }

        require_once "./models/TourModel.php";
        $this->model = new TourModel();
    }


    public function index($act)
    {
        $pageTitle = "Quản lý Tour";
        $tours = $this->model->getAll();   // tránh lỗi null
        $currentAct = $act;

        $keyword = $_GET['keyword'] ?? '';

        if ($keyword) {
            $tours = $this->model->searchByKeyword($keyword);
        } else {
            $tours = $this->model->getAll();
        }

        $view = "./views/admin/Tours/index.php";
        include "./views/layout/adminLayout.php";
    }

    public function create($act)
    {
        global $pdo;
        // Lấy danh mục Tour
        $categories = $pdo->query("SELECT id, name FROM tour_category")->fetchAll();
        $pageTitle = "Thêm Tour";
        $currentAct = $act;

        $view = "./views/admin/Tours/create.php";
        include "./views/layout/adminLayout.php";
    }

    public function store()
    {
        global $pdo;

        $imageName = null;

        // --- Xử lý upload ảnh ---
        if (!empty($_FILES["image_file"]["name"])) {

            $uploadDir = "assets/images/";
            $imageName = time() . "_" . basename($_FILES["image_file"]["name"]);
            $targetPath = $uploadDir . $imageName;

            move_uploaded_file($_FILES["image_file"]["tmp_name"], $targetPath);
        }

        // Lưu CSDL
        $sql = "INSERT INTO tours (code, title, short_desc, full_desc, base_price, duration_days,
                category_id, policy, supplier, image_url, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST["code"],
            $_POST["title"],
            $_POST["short_desc"],
            $_POST["full_desc"],
            $_POST["base_price"],
            $_POST["duration_days"],
            $_POST["category_id"],
            $_POST["policy"],
            $_POST["supplier"],
            $imageName,           // ← Lưu tên file vào database
            $_POST["is_active"]
        ]);

        header("Location: index.php?act=admin-tour");
        exit;
    }


    public function edit($act)
    {
        global $pdo;
        $cats = $pdo->query("SELECT id, name FROM tour_category")->fetchAll();

        $id = $_GET["id"];
        $tour = $this->model->find($id);
        $currentAct = $act;

        $pageTitle = "Sửa Tour";
        $view = "./views/admin/Tours/edit.php";
        include "./views/layout/adminLayout.php";
    }

    public function update()
    {
        global $pdo;

        $id = $_POST["id"];
        $imageName = $_POST["old_image"]; // giữ ảnh cũ nếu không upload

        // Nếu chọn ảnh mới → upload
        if (!empty($_FILES["image_file"]["name"])) {
            $uploadDir = "assets/images/";
            $imageName = time() . "_" . basename($_FILES["image_file"]["name"]);
            move_uploaded_file($_FILES["image_file"]["tmp_name"], $uploadDir . $imageName);
        }

        $sql = "UPDATE tours SET 
                    code=?, title=?, short_desc=?, full_desc=?, 
                    base_price=?, duration_days=?, category_id=?, 
                    policy=?, supplier=?, image_url=?, is_active=?
                WHERE id=?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST["code"],
            $_POST["title"],
            $_POST["short_desc"],
            $_POST["full_desc"],
            $_POST["base_price"],
            $_POST["duration_days"],
            $_POST["category_id"],
            $_POST["policy"],
            $_POST["supplier"],
            $imageName,  // <- LƯU TÊN ẢNH MỚI HOẶC ẢNH CŨ
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
}
