<?php
require_once "./models/BookingModel.php";

class BookingController {
    private $model;

    public function __construct()
    {
        require_once "./commons/env.php";
        require_once "./commons/function.php";

        $this->model = new BookingModel();
    }

    public function index($act = null) {
        $pageTitle = "Quản lý Booking";
        $bookings = $this->model->getAll();
        $currentAct = $act;

        $keyword = trim($_GET['keyword'] ?? '');

        if ($keyword !== '') {
            $bookings = $this->model->searchByKeyword($keyword);
        } else {
            $bookings = $this->model->getAll();
        }


        $view = "views/admin/Booking/index.php";
        include "./views/layout/adminLayout.php";
    }

    public function edit($act = null) {
        $id = $_GET['id'];
        $booking = $this->model->find($id);
        $schedules = $this->model->getSchedules(); // truyền schedules để select bật
        $pageTitle = "Sửa Booking";
        $currentAct = $act;
        $view = "views/admin/Booking/edit.php";
        include "./views/layout/adminLayout.php";
    }

    public function update() {
        $id = $_POST['id'];
        $this->model->update($id, $_POST);
        header("Location: index.php?act=admin-booking");
        exit;
    }

    public function delete() {
        $id = $_GET['id'];
        $this->model->delete($id);
        header("Location: index.php?act=admin-booking");
        exit;
    }
}
