<?php

require_once "./models/admin/StaffModel.php";
require_once "./models/admin/StaffRatingModel.php";
require_once "./models/admin/BookingModel.php";

class StaffRatingController
{
    private $staffModel;
    private $ratingModel;
    private $bookingModel;

    public function __construct()
    {
        $this->staffModel = new StaffModel();
        $this->ratingModel = new StaffRatingModel();
        $this->bookingModel = new BookingModel();
    }

    /**
     * Danh sách đánh giá của HDV
     */
    public function index($act)
    {
        $staff_id = $_GET['staff_id'] ?? null;

        if (!$staff_id) {
            $_SESSION['error'] = "❌ Không tìm thấy HDV!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $staff = $this->staffModel->find($staff_id);
        if (!$staff) {
            $_SESSION['error'] = "❌ HDV không tồn tại!";
            header("Location: index.php?act=admin-staff");
            exit;
        }

        $ratings = $this->ratingModel->getStaffRatings($staff_id);
        $statistics = $this->ratingModel->getRatingStatistics($staff_id);

        $pageTitle = "Đánh giá HDV - " . $staff['full_name'];
        $currentAct = $act;
        $view = "./views/admin/Staff/rating.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Form đánh giá HDV
     */
    public function create($act)
    {
        $booking_id = $_GET['booking_id'] ?? null;

        if (!$booking_id) {
            $_SESSION['error'] = "❌ Không tìm thấy booking!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $booking = $this->bookingModel->find($booking_id);
        if (!$booking) {
            $_SESSION['error'] = "❌ Booking không tồn tại!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        // Lấy HDV đã phân công cho tour này
        $pdo = $this->staffModel->getConnection();
        $sql = "SELECT s.id, u.full_name, sth.role
                FROM staff_tour_history sth
                JOIN staffs s ON s.id = sth.staff_id
                JOIN users u ON u.id = s.user_id
                WHERE sth.tour_schedule_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$booking['tour_schedule_id']]);
        $staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($staffs)) {
            $_SESSION['error'] = "❌ Tour này chưa phân công HDV!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $pageTitle = "Đánh giá HDV - Booking #" . $booking['booking_code'];
        $currentAct = $act;
        $view = "./views/admin/Staff/rating_create.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Lưu đánh giá
     */
    public function store()
    {
        $data = $_POST;
        $booking_id = $data['booking_id'] ?? null;

        if (!$booking_id) {
            $_SESSION['error'] = "❌ Thiếu thông tin booking!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        // Validate
        if (empty($data['staff_id']) || empty($data['rating'])) {
            $_SESSION['error'] = "❌ Vui lòng chọn HDV và nhập điểm đánh giá!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-rating-create&booking_id=" . $booking_id);
            exit;
        }

        $data['user_id'] = $_SESSION['user_id'] ?? null; // Admin đánh giá

        try {
            if ($this->ratingModel->addRating($data)) {
                $_SESSION['success'] = "✅ Đánh giá HDV thành công!";
            } else {
                throw new Exception("Không thể lưu đánh giá!");
            }

            header("Location: index.php?act=admin-staff-rating&staff_id=" . $data['staff_id']);
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-staff-rating-create&booking_id=" . $booking_id);
            exit;
        }
    }
}
?>