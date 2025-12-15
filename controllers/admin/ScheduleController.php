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

        foreach ($schedules as &$s) {
            $s['booking_count'] = $this->model->getBookingCount($s['id']);
        }

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
        error_log("=== SCHEDULE STORE DEBUG ===");
        error_log("POST data: " . print_r($_POST, true));

        $result = $this->model->store($_POST);

        error_log("Store result: " . ($result ? "SUCCESS" : "FAILED"));

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
        error_log("=== SCHEDULE UPDATE DEBUG ===");
        error_log("POST data: " . print_r($_POST, true));

        $id = $_POST["id"];
        $result = $this->model->update($id, $_POST);

        error_log("Update result: " . ($result ? "SUCCESS" : "FAILED"));

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
