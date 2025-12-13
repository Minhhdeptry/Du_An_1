<?php
require_once "./models/admin/ItineraryModel.php";
require_once "./models/admin/TourModel.php";

class ItineraryController
{
    private $model;
    private $tourModel;

    public function __construct()
    {
        $this->model = new ItineraryModel();
        $this->tourModel = new TourModel();
    }

    // Danh sách lịch trình của 1 tour
    public function index($act)
    {
        $tour_id = $_GET['tour_id'] ?? null;
        if (!$tour_id) {
            header("Location: index.php?act=admin-itinerary");
            exit;
        }

        $tour = $this->tourModel->find($tour_id);
        if (!$tour) {
            header("Location: index.php?act=admin-itinerary");
            exit;
        }

        $itineraries = $this->model->getByTour($tour_id);
        $pageTitle = "Lịch trình: " . $tour['title'];
        $currentAct = $act;
        $view = "views/admin/Itinerary/index.php";
        include "./views/layout/adminLayout.php";
    }

    // Form thêm ngày mới
    public function create($act)
    {
        $tour_id = $_GET['tour_id'] ?? null;
        if (!$tour_id) {
            header("Location: index.php?act=admin-itinerary");
            exit;
        }

        $tour = $this->tourModel->find($tour_id);
        $nextDay = $this->model->getTotalDays($tour_id) + 1;

        $pageTitle = "Thêm lịch trình ngày {$nextDay}";
        $currentAct = $act;
        $view = "views/admin/Itinerary/create.php";
        include "./views/layout/adminLayout.php";
    }

    // Lưu ngày mới
    public function store()
    {
        $tour_id = $_POST['tour_id'] ?? null;
        if ($this->model->store($_POST)) {
            $_SESSION['success'] = "✅ Đã thêm lịch trình ngày " . $_POST['day_number'];
        } else {
            $_SESSION['error'] = "❌ Thêm lịch trình thất bại!";
        }
        header("Location: index.php?act=admin-itinerary&tour_id={$tour_id}");
        exit;
    }

    // Form sửa
    public function edit($act)
    {
        $id = $_GET['id'] ?? null;
        $itinerary = $this->model->find($id);

        if (!$itinerary) {
            header("Location: index.php?act=admin-itinerary");
            exit;
        }

        $tour = $this->tourModel->find($itinerary['tour_id']);
        $pageTitle = "Sửa lịch trình ngày " . $itinerary['day_number'];
        $currentAct = $act;
        $view = "views/admin/Itinerary/edit.php";
        include "./views/layout/adminLayout.php";
    }

    // Cập nhật
    public function update()
    {
        $id = $_POST['id'] ?? null;
        $tour_id = $_POST['tour_id'] ?? null;

        if ($this->model->update($id, $_POST)) {
            $_SESSION['success'] = "✅ Cập nhật lịch trình thành công!";
        } else {
            $_SESSION['error'] = "❌ Cập nhật thất bại!";
        }
        header("Location: index.php?act=admin-itinerary&tour_id={$tour_id}");
        exit;
    }

    // Trang chọn tour (khi vào từ sidebar)
    public function selectTour($act)
    {
        $keyword = trim($_GET['keyword'] ?? '');

        if ($keyword) {
            $tours = $this->tourModel->searchByKeywordWithStatus($keyword);
        } else {
            $tours = $this->tourModel->getAllWithCategoryStatus();
        }

        $pageTitle = "Chọn Tour để quản lý lịch trình";
        $currentAct = $act;
        $view = "views/admin/Itinerary/select.php";
        include "./views/layout/adminLayout.php";
    }

    // Xóa
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        $tour_id = $_GET['tour_id'] ?? null;

        if ($this->model->delete($id)) {
            $_SESSION['success'] = "✅ Đã xóa lịch trình!";
        } else {
            $_SESSION['error'] = "❌ Xóa thất bại!";
        }
        header("Location: index.php?act=admin-itinerary&tour_id={$tour_id}");
        exit;
    }
}