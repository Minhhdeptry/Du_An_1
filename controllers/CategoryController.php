<?php
class CategoryController
{
    private $model;

    public function __construct()
    {
        require_once "./models/CategoryModel.php";
        $this->model = new CategoryModel();
    }

    public function index($act)
    {
        $pageTitle = "Quản lý Danh mục Tour";
        $categories = $this->model->getAll();
        $currentAct = $act;


        $view = "./views/admin/Category/index.php";
        include "./views/layout/adminLayout.php";
    }

    public function create($act)
    {
        $pageTitle = "Thêm danh mục";
        $currentAct = $act;

        $view = "./views/admin/Category/create.php";
        include "./views/layout/adminLayout.php";
    }

    public function store()
    {
        if (!isset($_POST["name"])) {
            die("Thiếu dữ liệu");
        }

        $this->model->store($_POST);

        header("Location: index.php?act=admin-category");
        exit;
    }

    public function edit($act)
    {
        $id = $_GET["id"] ?? null;
        if (!$id) {
            header("Location: index.php?act=admin-category");
            exit;
        }

        $category = $this->model->find($id);
        if (!$category) {
            header("Location: index.php?act=admin-category");
            exit;
        }

        $pageTitle = "Sửa danh mục";
        $currentAct = $act;

        // biến truyền vào view phải đặt giống trong view: $category
        $view = "./views/admin/Category/edit.php";
        include "./views/layout/adminLayout.php";
    }

    public function update()
    {
        if (!isset($_POST["id"])) {
            die("Thiếu ID");
        }

        $id = $_POST["id"];
        $this->model->update($id, $_POST);

        header("Location: index.php?act=admin-category");
        exit;
    }

    public function delete()
    {
        $id = $_GET["id"] ?? null;
        if ($id) {
            $this->model->delete($id);
        }

        header("Location: index.php?act=admin-category");
        exit;
    }
}
