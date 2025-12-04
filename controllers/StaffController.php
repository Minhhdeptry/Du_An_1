<?php
// controllers/StaffController.php

require_once __DIR__ . "/../models/StaffModel.php";
require_once __DIR__ . "/../models/UserModel.php";

class StaffController
{
    private $staffModel;
    private $userModel;

    public function __construct()
    {
        // models tự tạo PDO trong __construct của chính nó (theo style project)
        require_once __DIR__ . "/../commons/function.php";
        $this->staffModel = new StaffModel();
        $this->userModel = new UserModel();
    }

    // danh sách staff
    public function index($act = null)
    {
        $pageTitle = "Quản lý Nhân viên";
        $currentAct = $act;

        $keyword = trim($_GET['keyword'] ?? '');

        if ($keyword !== '') {
            $staffs = $this->staffModel->search($keyword);
        } else {
            $staffs = $this->staffModel->getAll(); // chỉ lấy HDV
        }

        $view = "./views/admin/Staff/index.php";
        include "./views/layout/adminLayout.php";
    }


    // form thêm
    public function create($act = null)
    {
        $pageTitle = "Thêm Nhân viên";
        $currentAct = $act;

        // lấy danh sách HDV
        $users = $this->userModel->getUsersByRole('HDV');

        $view = "./views/admin/Staff/create.php";
        include "./views/layout/adminLayout.php";
    }


    // lưu mới
    public function store()
    {
        // validate cơ bản
        $data = $_POST;
        if (empty($data['user_id'])) {
            // bạn có thể redirect lại kèm thông báo; tạm redirect về create
            header("Location: index.php?act=admin-staff-create");
            exit;
        }

        $this->staffModel->store($data);

        header("Location: index.php?act=admin-staff");
        exit;
    }

    // form sửa
    public function edit($act = null)
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $staff = $this->staffModel->find($id);
        if (!$staff) {
            header("Location: index.php?act=admin-staff");
            exit;
        }

        // lấy danh sách HDV
        $users = $this->userModel->getUsersByRole('HDV');

        $pageTitle = "Sửa Nhân viên";
        $currentAct = $act;
        $view = "./views/admin/Staff/edit.php";
        include "./views/layout/adminLayout.php";
    }


    // cập nhật
    public function update()
    {
        $data = $_POST;
        $id = $data['id'] ?? null;
        if (!$id) {
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $this->staffModel->update($data);

        header("Location: index.php?act=admin-staff");
        exit;
    }

    // xóa
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->staffModel->delete($id);
        }

        header("Location: index.php?act=admin-staff");
        exit;
    }
}
