<?php
class ScheduleController
{
    private $model;
    private $tourModel;

    public function __construct()
    {
        require_once "./models/admin/TourScheduleModel.php";
        require_once "./models/admin/TourModel.php";

        $this->model = new TourScheduleModel();
        $this->tourModel = new TourModel();
    }

    public function index($act)
    {
        $pageTitle = "Quản lý Lịch khởi hành";
        $currentAct = $act;

        $keyword = trim($_GET['keyword'] ?? '');
        $schedules = $keyword !== ''
            ? $this->model->searchByKeyword($keyword)
            : $this->model->getAll();

        $view = "./views/admin/Schedule/index.php";
        include "./views/layout/adminLayout.php";
    }

    public function create($act)
    {
        $pageTitle = "Thêm lịch khởi hành";
        $tours = $this->tourModel->getAll();
        $currentAct = $act;

        $view = "./views/admin/Schedule/create.php";
        include "./views/layout/adminLayout.php";
    }

    public function store()
    {
        $this->model->store($_POST); // store đã tự set seats_available
        header("Location: index.php?act=admin-schedule");
        exit;
    }

    public function edit($act)
    {
        $id = $_GET["id"];
        $schedule = $this->model->find($id);
        $tours = $this->tourModel->getAll();

        $pageTitle = "Sửa lịch";
        $currentAct = $act;

        $view = "./views/admin/Schedule/edit.php";
        include "./views/layout/adminLayout.php";
    }

    public function update()
    {
        $id = $_POST["id"];
        $this->model->update($id, $_POST); // updateSeats tự gọi trong model
        header("Location: index.php?act=admin-schedule");
        exit;
    }

    public function delete()
    {
        $id = $_GET["id"];

        if ($this->model->hasBooking($id)) {
            echo "Không thể xóa lịch này vì còn booking.";
            exit;
        }

        $this->model->delete($id);
        header("Location: index.php?act=admin-schedule");
        exit;
    }

}
