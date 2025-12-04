<?php
class ScheduleController
{
    private $model;
    private $tourModel;
    public function __construct()
    {
        require_once "./models/TourScheduleModel.php";
        require_once "./models/TourModel.php";

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
        $this->model->store($_POST);
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
        $this->model->update($id, $_POST);

        header("Location: index.php?act=admin-schedule");
        exit;
    }

    public function delete()
    {
        $id = $_GET["id"];
        $this->model->delete($id);

        header("Location: index.php?act=admin-schedule");
        exit;
    }
}
