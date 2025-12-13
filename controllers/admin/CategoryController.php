<?php
class CategoryController
{
    private $model;

    public function __construct()
    {
        require_once "./models/admin/CategoryModel.php";
        $this->model = new CategoryModel();
    }

    public function index($act)
    {
        $keyword = $_GET['keyword'] ?? '';
        $categories = $this->model->getAll($keyword);
        $pageTitle = "Quản lý Danh mục Tour";
        $currentAct = $act;

        $view = "./views/admin/Category/index.php";
        include "./views/layout/adminLayout.php";
    }

    public function create($act)
    {
        $pageTitle = "Thêm danh mục Tour";
        $currentAct = $act;

        $view = "./views/admin/Category/create.php";
        include "./views/layout/adminLayout.php";
    }

    public function store()
    {
        if (!isset($_POST['code']) || !isset($_POST['name'])) {
            die("Thiếu dữ liệu");
        }

        // Check duplicate code
        if ($this->model->codeExists($_POST['code'])) {
            die("Mã danh mục đã tồn tại!");
        }

        $this->model->store($_POST);
        header("Location: index.php?act=admin-category");
        exit;
    }

    public function edit($act)
    {
        $id = $_GET['id'] ?? null;
        if (!$id) header("Location: index.php?act=admin-category");

        $category = $this->model->find($id);
        if (!$category) header("Location: index.php?act=admin-category");

        $pageTitle = "Sửa danh mục Tour";
        $currentAct = $act;
        $view = "./views/admin/Category/edit.php";
        include "./views/layout/adminLayout.php";
    }

    public function update()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) die("Thiếu ID");

        // Check duplicate code
        if ($this->model->codeExists($_POST['code'], $id)) {
            die("Mã danh mục đã tồn tại!");
        }

        $this->model->update($id, $_POST);
        header("Location: index.php?act=admin-category");
        exit;
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $success = $this->model->delete($id);
            if (!$success) {
                die("Danh mục đang có tour, không thể xóa!");
            }
        }
        header("Location: index.php?act=admin-category");
        exit;
    }
}