<?php
class PaymentController
{
    private $model;

    public function __construct()
    {
        require_once "./models/admin/PaymentModel.php";
        $this->model = new PaymentModel();
    }

    // ============ TRANG 1: LIST PAYMENT (không theo booking) ============
    public function index($act)
    {
        $currentAct = $act;

        if (isset($_GET['booking_id'])) {
            $booking_id = $_GET['booking_id'];
            $payments = $this->model->getPaymentsByBooking($booking_id);
            $title = "Lịch sử thanh toán (#{$booking_id})";
        } else {
            $payments = $this->model->getAllPayments();
            $title = "Danh sách thanh toán";
        }

        $view = "./views/admin/Payment/index.php";
        include "./views/layout/adminLayout.php";
    }

    // ============ TRANG 2: HISTORY PAYMENT THEO BOOKING ============
    public function history($act)
    {
        $booking_id = $_GET['booking_id'] ?? null;
        $booking = $booking_id ? $this->model->getBookingInfo($booking_id) : null;
        $payments = $booking_id ? $this->model->getPaymentsByBooking($booking_id) : $this->model->getAllPayments();
        $currentAct = $act;

        $view = "./views/admin/Payment/history.php";
        include "./views/layout/adminLayout.php";

    }

    // ============ XÁC NHẬN ============

    public function confirm()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            // Xác nhận payment
            $this->model->confirmPayment($id);

            // ✅ THÊM: Lấy booking_id và cập nhật trạng thái
            $payment = $this->model->getPaymentById($id);
            if ($payment && !empty($payment['booking_id'])) {
                require_once "./models/admin/BookingModel.php";
                $bookingModel = new BookingModel();
                $bookingModel->updateBookingStatusByPayment($payment['booking_id']);
            }
        }
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }


    public function cancel()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->model->cancelPayment($id);
        }
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }


}
?>