<?php
require_once "./models/BookingModel.php";
require_once "./models/BookingItemModel.php";

class BookingController
{
    private $model;
    private $itemModel;

    public function __construct()
    {
        require_once "./commons/env.php";
        require_once "./commons/function.php";

        $this->model = new BookingModel();
        $this->itemModel = new BookingItemModel();
    }

    public function index($act)
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $bookings = $keyword ? $this->model->searchByKeyword($keyword) : $this->model->getAll();

        $currentAct = $act;
        $pageTitle = "Quản lý Booking";
        $view = "views/admin/Booking/index.php";
        include "./views/layout/adminLayout.php";
    }

    public function create($act)
    {
        $schedules_raw = $this->model->getSchedules();
        $schedules = [];
        foreach ($schedules_raw as $sc) {
            $schedules[] = [
                'id' => $sc['id'],
                'depart_date' => $sc['depart_date'],
                'seats_available' => (int) ($sc['seats_available'] ?? 0),
                'price_adult' => (float) ($sc['price_adult'] ?? 0),
                'price_children' => (float) ($sc['price_children'] ?? 0),
                'tour_title' => $sc['tour_title'] ?? 'Unknown'
            ];
        }

        $pageTitle = "Tạo Booking";
        $currentAct = $act;
        $view = "views/admin/Booking/create.php";
        include "./views/layout/adminLayout.php";
    }


    public function edit($act)
    {
        $id = $_GET['id'];
        $booking = $this->model->find($id);
        $schedules = $this->model->getSchedules();
        $pageTitle = "Sửa Booking";

        $currentAct = $act;
        $view = "views/admin/Booking/edit.php";
        include "./views/layout/adminLayout.php";
    }

    public function update()
    {
        if ($this->model->update($_POST['id'], $_POST)) {
            header("Location: index.php?act=admin-booking");
            exit;
        } else {
            echo "Không đủ chỗ hoặc dữ liệu không hợp lệ!";
        }
    }

    public function delete()
    {
        $id = $_GET['id'];
        $this->model->delete($id);
        header("Location: index.php?act=admin-booking");
        exit;
    }

    public function cancel()
    {
        $id = $_GET['id'];
        $this->model->cancelBooking($id);
        header("Location: index.php?act=admin-booking");
        exit;
    }
}
