<?php
// controllers/admin/BookingCustomerController.php

require_once "./commons/function.php";
require_once "./models/admin/BookingCustomerModel.php";
require_once "./models/admin/BookingModel.php";

class BookingCustomerController
{
    private BookingCustomerModel $model;
    private BookingModel $bookingModel;
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = connectDB();

        $this->model = new BookingCustomerModel($this->pdo);
        $this->bookingModel = new BookingModel($this->pdo);
    }

    /**
     * Danh sách khách trong booking
     */
    public function index($act = null)
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

        $customers = $this->model->getByBooking($booking_id);
        $counts = $this->model->countByType($booking_id);
        $validation = $this->model->validateCount($booking_id);

        $pageTitle = "Danh sách khách - " . $booking['booking_code'];
        $currentAct = $act;
        $view = "./views/admin/BookingCustomer/index.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Form thêm khách
     */
    public function create($act = null)
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

        $pageTitle = "Thêm khách - " . $booking['booking_code'];
        $currentAct = $act;
        $view = "./views/admin/BookingCustomer/create.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Lưu khách mới
     */
    public function store()
    {
        $data = $_POST;
        $booking_id = $data['booking_id'] ?? null;

        if (!$booking_id) {
            $_SESSION['error'] = "❌ Không tìm thấy booking!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        // Validate
        if (empty($data['full_name'])) {
            $_SESSION['error'] = "❌ Họ tên không được để trống!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-booking-customer-create&booking_id={$booking_id}");
            exit;
        }

        // Kiểm tra số lượng
        $counts = $this->model->countByType($booking_id);
        $booking = $this->bookingModel->find($booking_id);
        
        $customer_type = $data['customer_type'];
        
        if ($customer_type === 'ADULT' && $counts['ADULT'] >= $booking['adults']) {
            $_SESSION['error'] = "❌ Đã đủ số người lớn ({$booking['adults']})!";
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-booking-customer-create&booking_id={$booking_id}");
            exit;
        }
        
        if (in_array($customer_type, ['CHILD', 'INFANT'])) {
            $total_children = $counts['CHILD'] + $counts['INFANT'];
            if ($total_children >= $booking['children']) {
                $_SESSION['error'] = "❌ Đã đủ số trẻ em ({$booking['children']})!";
                $_SESSION['old_data'] = $data;
                header("Location: index.php?act=admin-booking-customer-create&booking_id={$booking_id}");
                exit;
            }
        }

        try {
            $this->model->store($data);
            $_SESSION['success'] = "✅ Thêm khách thành công!";
            header("Location: index.php?act=admin-booking-customer&booking_id={$booking_id}");
        } catch (Exception $e) {
            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-booking-customer-create&booking_id={$booking_id}");
        }
        exit;
    }

    /**
     * Form sửa khách
     */
    public function edit($act = null)
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy khách!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $customer = $this->model->find($id);
        
        if (!$customer) {
            $_SESSION['error'] = "❌ Khách không tồn tại!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $booking = $this->bookingModel->find($customer['booking_id']);

        $pageTitle = "Sửa thông tin khách - " . $customer['full_name'];
        $currentAct = $act;
        $view = "./views/admin/BookingCustomer/edit.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * Cập nhật khách
     */
    public function update()
    {
        $data = $_POST;
        $id = $data['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy khách!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $customer = $this->model->find($id);
        
        if (!$customer) {
            $_SESSION['error'] = "❌ Khách không tồn tại!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        try {
            $this->model->update($data);
            $_SESSION['success'] = "✅ Cập nhật thành công!";
            header("Location: index.php?act=admin-booking-customer&booking_id={$customer['booking_id']}");
        } catch (Exception $e) {
            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
            $_SESSION['old_data'] = $data;
            header("Location: index.php?act=admin-booking-customer-edit&id={$id}");
        }
        exit;
    }

    /**
     * Xóa khách
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy khách!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $customer = $this->model->find($id);
        
        if (!$customer) {
            $_SESSION['error'] = "❌ Khách không tồn tại!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $booking_id = $customer['booking_id'];

        if ($this->model->delete($id)) {
            $_SESSION['success'] = "✅ Đã xóa khách: " . $customer['full_name'];
        } else {
            $_SESSION['error'] = "❌ Không thể xóa!";
        }

        header("Location: index.php?act=admin-booking-customer&booking_id={$booking_id}");
        exit;
    }

    /**
     * Check-in khách
     */
    public function checkIn()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy khách!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $customer = $this->model->find($id);
        
        if (!$customer) {
            $_SESSION['error'] = "❌ Khách không tồn tại!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        if ($this->model->checkIn($id)) {
            $_SESSION['success'] = "✅ Check-in thành công: " . $customer['full_name'];
        } else {
            $_SESSION['error'] = "❌ Check-in thất bại!";
        }

        header("Location: index.php?act=admin-booking-customer&booking_id={$customer['booking_id']}");
        exit;
    }

    /**
     * Hủy check-in
     */
    public function undoCheckIn()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = "❌ Không tìm thấy khách!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $customer = $this->model->find($id);
        
        if (!$customer) {
            $_SESSION['error'] = "❌ Khách không tồn tại!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        if ($this->model->undoCheckIn($id)) {
            $_SESSION['success'] = "✅ Đã hủy check-in: " . $customer['full_name'];
        } else {
            $_SESSION['error'] = "❌ Hủy check-in thất bại!";
        }

        header("Location: index.php?act=admin-booking-customer&booking_id={$customer['booking_id']}");
        exit;
    }
}