<?php
require_once "./models/admin/PaymentModel.php";
require_once "./models/admin/BookingModel.php";

class PaymentController
{
    private $paymentModel;
    private $bookingModel;

    public function __construct()
    {
        require_once "./commons/function.php";
        $pdo = connectDB();
        $this->paymentModel = new PaymentModel($pdo);
        $this->bookingModel = new BookingModel($pdo);
    }

    // ============ DANH SÁCH TẤT CẢ PAYMENTS ============
    public function index($act)
    {
        $pageTitle = "Quản lý Thanh toán";
        $currentAct = $act;

        // Lấy tất cả payments kèm thông tin booking
        $payments = $this->paymentModel->getAll();

        $view = "./views/admin/Payment/index.php";
        include "./views/layout/adminLayout.php";
    }

    // ============ LỊCH SỬ THANH TOÁN THEO BOOKING ============
    public function history($act)
    {
        $pageTitle = "Lịch sử Thanh toán";
        $currentAct = $act;

        $booking_id = $_GET['booking_id'] ?? null;

        if ($booking_id) {
            $booking = $this->bookingModel->find($booking_id);
            $payments = $this->paymentModel->getByBooking($booking_id);
            
            if ($booking) {
                $booking['payment_status'] = $this->paymentModel->getPaymentStatus($booking_id);
                $booking['total_paid'] = $this->paymentModel->getTotalPaid($booking_id);
                $booking['remaining'] = $booking['total_amount'] - $booking['total_paid'];
            }
        } else {
            $booking = null;
            $payments = [];
        }

        $view = "./views/admin/Payment/history.php";
        include "./views/layout/adminLayout.php";
    }

    // ============ FORM TẠO PAYMENT THỦ CÔNG ============
    public function createForm($act)
    {
        $booking_id = (int) ($_GET['booking_id'] ?? 0);
        
        if (!$booking_id) {
            $_SESSION['error'] = "❌ Booking ID không hợp lệ!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $booking = $this->bookingModel->find($booking_id);
        
        if (!$booking) {
            $_SESSION['error'] = "❌ Booking không tồn tại!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        // Lấy các payments đã có
        $existingPayments = $this->paymentModel->getByBooking($booking_id);
        $totalPaid = $this->paymentModel->getTotalPaid($booking_id);
        $remaining = (float) $booking['total_amount'] - $totalPaid;

        $pageTitle = "Thêm Payment - Booking #" . $booking['booking_code'];
        $currentAct = $act;
        $view = "./views/admin/Payment/create.php";
        include "./views/layout/adminLayout.php";
    }

    // ============ XỬ LÝ TẠO PAYMENT ============
    public function store()
    {
        $data = $_POST;
        $res = $this->paymentModel->create($data);

        if ($res['ok'] ?? false) {
            $_SESSION['success'] = "✅ Thêm payment thành công!";
            
            // Redirect về booking detail hoặc payment history
            if (!empty($data['booking_id'])) {
                header("Location: index.php?act=admin-payment-history&booking_id=" . $data['booking_id']);
            } else {
                header("Location: index.php?act=admin-payment");
            }
            exit;
        }

        $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Tạo payment thất bại']);
        header("Location: index.php?act=admin-payment-create&booking_id=" . ($data['booking_id'] ?? ''));
        exit;
    }

    // ============ FORM SỬA PAYMENT ============
    public function editForm($act)
    {
        $payment_id = (int) ($_GET['id'] ?? 0);
        
        if (!$payment_id) {
            $_SESSION['error'] = "❌ Payment ID không hợp lệ!";
            header("Location: index.php?act=admin-payment");
            exit;
        }

        $payment = $this->paymentModel->find($payment_id);
        
        if (!$payment) {
            $_SESSION['error'] = "❌ Payment không tồn tại!";
            header("Location: index.php?act=admin-payment");
            exit;
        }

        $booking = $this->bookingModel->find($payment['booking_id']);

        $pageTitle = "Sửa Payment #" . ($payment['payment_code'] ?? 'N/A');
        $currentAct = $act;
        $view = "./views/admin/Payment/edit.php";
        include "./views/layout/adminLayout.php";
    }

    // ============ XỬ LÝ CẬP NHẬT PAYMENT ============
    public function update()
    {
        $payment_id = (int) ($_POST['id'] ?? 0);
        $data = $_POST;

        if (!$payment_id) {
            $_SESSION['error'] = "❌ Payment ID không hợp lệ!";
            header("Location: index.php?act=admin-payment");
            exit;
        }

        $res = $this->paymentModel->update($payment_id, $data);

        if ($res['ok'] ?? false) {
            $_SESSION['success'] = "✅ Cập nhật payment thành công!";
            
            if (!empty($data['booking_id'])) {
                header("Location: index.php?act=admin-payment-history&booking_id=" . $data['booking_id']);
            } else {
                header("Location: index.php?act=admin-payment");
            }
            exit;
        }

        $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Cập nhật thất bại']);
        header("Location: index.php?act=admin-payment-edit&id=" . $payment_id);
        exit;
    }

    // ============ XÓA PAYMENT ============
    public function delete()
    {
        $payment_id = (int) ($_GET['id'] ?? 0);
        
        if (!$payment_id) {
            $_SESSION['error'] = "❌ Payment ID không hợp lệ!";
            header("Location: index.php?act=admin-payment");
            exit;
        }

        // Lấy thông tin payment để redirect về booking
        $payment = $this->paymentModel->find($payment_id);
        $booking_id = $payment['booking_id'] ?? null;

        $res = $this->paymentModel->delete($payment_id);

        if ($res['ok'] ?? false) {
            $_SESSION['success'] = "✅ Đã xóa payment thành công!";
        } else {
            $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Xóa payment thất bại']);
        }

        if ($booking_id) {
            header("Location: index.php?act=admin-payment-history&booking_id=" . $booking_id);
        } else {
            header("Location: index.php?act=admin-payment");
        }
        exit;
    }

    // ============ XÁC NHẬN THANH TOÁN (SUCCESS) ============
    public function confirm()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy ID thanh toán!";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?act=admin-payment'));
            exit;
        }

        $payment = $this->paymentModel->find($id);
        
        if (!$payment) {
            $_SESSION['error'] = "❌ Thanh toán không tồn tại!";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?act=admin-payment'));
            exit;
        }

        // Cập nhật status thành SUCCESS
        $result = $this->paymentModel->update($id, [
            'booking_id' => $payment['booking_id'],
            'amount' => $payment['amount'],
            'type' => $payment['type'],
            'method' => $payment['method'],
            'status' => 'SUCCESS',
            'paid_at' => date('Y-m-d H:i:s'),
            'note' => $payment['note'] ?? ''
        ]);

        if ($result['ok'] ?? false) {
            $_SESSION['success'] = "✅ Xác nhận thanh toán thành công!";
        } else {
            $_SESSION['error'] = "❌ Xác nhận thất bại!";
        }

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?act=admin-payment'));
        exit;
    }

    // ============ HỦY THANH TOÁN (REFUNDED) ============
    public function cancel()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy ID thanh toán!";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?act=admin-payment'));
            exit;
        }

        $payment = $this->paymentModel->find($id);
        
        if (!$payment) {
            $_SESSION['error'] = "❌ Thanh toán không tồn tại!";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?act=admin-payment'));
            exit;
        }

        // Cập nhật status thành REFUNDED
        $result = $this->paymentModel->update($id, [
            'booking_id' => $payment['booking_id'],
            'amount' => $payment['amount'],
            'type' => $payment['type'],
            'method' => $payment['method'],
            'status' => 'REFUNDED',
            'paid_at' => $payment['paid_at'],
            'note' => $payment['note'] ?? ''
        ]);

        if ($result['ok'] ?? false) {
            $_SESSION['success'] = "✅ Đã hủy thanh toán!";
        } else {
            $_SESSION['error'] = "❌ Hủy thất bại!";
        }

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?act=admin-payment'));
        exit;
    }

    // ============ HELPER: FORMAT ERRORS ============
    private function formatErrors(array $errors): string
    {
        if (empty($errors)) {
            return 'Có lỗi xảy ra!';
        }

        return '<ul class="mb-0">' .
            implode('', array_map(function ($err) {
                return '<li>' . htmlspecialchars($err) . '</li>';
            }, $errors)) .
            '</ul>';
    }
}