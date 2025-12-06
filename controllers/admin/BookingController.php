<?php
require_once "./models/admin/BookingModel.php";

class BookingController
{
    private $model;

    public function __construct()
    {
        $this->model = new BookingModel();
    }

    /** Danh sách booking */
    public function index(string $act): void
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $bookings = $keyword 
            ? $this->model->searchByKeyword($keyword) 
            : $this->model->getAll();

        $pageTitle = "Quản lý Booking";
        $currentAct = $act;
        $view = "views/admin/Booking/index.php";
        include "./views/layout/adminLayout.php";
    }

    /** Form sửa booking */
    public function edit(string $act): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $booking = $this->model->find($id);

        if (!$booking) {
            echo "<p class='text-danger'>Booking không tồn tại!</p>";
            return;
        }

        $schedules_raw = $this->model->getSchedules();
        $schedules = array_map(function ($sc) {
            return [
                'id' => (int) $sc['id'],
                'depart_date' => $sc['depart_date'],
                'seats_available' => (int) $sc['seats_available'],
                'price_adult' => (float) $sc['price_adult'],
                'price_children' => (float) $sc['price_children'],
                'tour_title' => $sc['tour_title']
            ];
        }, $schedules_raw);

        $pageTitle = "Sửa Booking";
        $currentAct = $act;
        $view = "views/admin/Booking/edit.php";
        include "./views/layout/adminLayout.php";
    }

    /** Xử lý cập nhật booking */
    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $_POST['adults'] = (int) ($_POST['adults'] ?? 0);
        $_POST['children'] = (int) ($_POST['children'] ?? 0);

        $result = $this->model->update($id, $_POST);

        if ($result['ok'] ?? false) {
            header("Location: index.php?act=admin-booking");
            exit;
        }

        echo "<p class='text-danger'>" . htmlspecialchars($result['error'] ?? 'Cập nhật thất bại') . "</p>";
    }

    /** Hủy booking */
    public function cancel(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($this->model->cancelBooking($id)) {
            header("Location: index.php?act=admin-booking");
            exit;
        }

        echo "<p class='text-danger'>Hủy booking thất bại!</p>";
    }
}
