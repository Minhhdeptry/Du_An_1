<?php
require_once "./models/admin/BookingModel.php";
require_once "./models/admin/BookingItemModel.php";

class BookingController
{
    private $bookingModel;
    private $itemModel;

    public function __construct()
    {
        $this->bookingModel = new BookingModel();
        $this->itemModel = new BookingItemModel();
    }

    /** ------------------------
     *  Danh sách booking
     */
    // controllers/admin/BookingController.php

    public function index(string $act): void
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $bookings = $keyword
            ? $this->bookingModel->searchByKeyword($keyword)
            : $this->bookingModel->getAll();

        // ✅ THÊM: Lấy trạng thái thanh toán cho mỗi booking
        foreach ($bookings as &$booking) {
            $booking['payment_status'] = $this->bookingModel->getPaymentStatus($booking['id']);
        }

        // Lấy trạng thái từ Model
        $statusText = BookingModel::$statusLabels;
        $statusColor = [
            'PENDING' => 'warning',
            'CONFIRMED' => 'primary',
            'PAID' => 'info',
            'COMPLETED' => 'success',
            'CANCELED' => 'danger',
        ];

        $pageTitle = "Quản lý Booking";
        $currentAct = $act;
        $view = "views/admin/Booking/index.php";
        include "./views/layout/adminLayout.php";
    }

    /** ------------------------
     *  ✅ FIX: Form tạo booking - Lấy tours + adult_price, child_price
     */
    public function createForm(string $act): void
    {
        // ✅ Lấy danh sách TOUR với GIÁ
        $tours = $this->getToursList();

        if (empty($tours)) {
            $_SESSION['error'] = "⚠️ Chưa có tour nào trong hệ thống!";
            header("Location: index.php?act=admin-tour");
            exit;
        }

        $pageTitle = "Tạo Booking (Tour theo yêu cầu)";
        $currentAct = $act;
        $view = "views/admin/Booking/create.php";
        include "./views/layout/adminLayout.php";
    }

    /** ------------------------
     *  ✅ FIX: Helper - Lấy tours kèm giá adult_price, child_price
     */
    private function getToursList(): array
    {
        try {
            $stmt = $this->bookingModel->getConnection()->prepare("
                SELECT t.id, t.code, t.title, t.duration_days,
                       t.adult_price, t.child_price,
                       c.name AS category_name
                FROM tours t
                LEFT JOIN tour_category c ON c.id = t.category_id
                WHERE t.is_active = 1
                ORDER BY t.title ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("GetToursList Error: " . $e->getMessage());
            return [];
        }
    }

    /** ------------------------
     *  Xử lý tạo booking
     */
    public function store(): void
    {
        // ✅ Lấy author_id từ session
        $author_id = $_SESSION['user_id'] ?? null;

        $data = $_POST;

        // ✅ THÊM: Kiểm tra chỗ trống TRƯỚC KHI TẠO
        // (Chỉ check với tour thường, tour custom thì bỏ qua)
        if (!empty($data['tour_id'])) {
            // Lấy schedule_id từ tour
            $tour_id = (int) $data['tour_id'];
            $adults = (int) ($data['adults'] ?? 0);
            $children = (int) ($data['children'] ?? 0);

            // Tìm schedule gần nhất của tour này
            $stmt = $this->bookingModel->getConnection()->prepare("
            SELECT id FROM tour_schedule 
            WHERE tour_id = ? 
              AND depart_date >= CURDATE() 
              AND status = 'OPEN'
              AND is_custom_request = 0
            ORDER BY depart_date ASC 
            LIMIT 1
        ");
            $stmt->execute([$tour_id]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($schedule) {
                $schedule_id = $schedule['id'];

                // Check capacity
                if (!$this->bookingModel->checkCapacity($schedule_id, $adults, $children)) {
                    $_SESSION['error'] = "❌ Không đủ chỗ! Tour này đã hết chỗ. Vui lòng chọn tour khác.";
                    $_SESSION['old_data'] = $data;
                    header("Location: index.php?act=admin-booking-create");
                    exit;
                }
            }
        }

        // ✅ Tiếp tục tạo booking
        $res = $this->bookingModel->create($data, $author_id);

        if ($res['ok'] ?? false) {
            $booking_id = $res['booking_id'];

            // ✅ Thêm items nếu có (dịch vụ bổ sung)
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (!empty($item['description']) && !empty($item['qty'])) {
                        $this->itemModel->addItem(
                            $booking_id,
                            $item['description'],
                            (int) ($item['qty']),
                            (float) ($item['unit_price'] ?? 0),
                            $item['type'] ?? 'SERVICE'
                        );
                    }
                }
            }

            // ✅ Flash message với booking_code từ response
            $booking = $this->bookingModel->find($booking_id);
            $_SESSION['success'] = "✅ Tạo booking thành công! Mã: " . ($booking['booking_code'] ?? 'N/A');
            header("Location: index.php?act=admin-booking");
            exit;
        }

        // ❌ Xử lý lỗi - Hiển thị lại form
        $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Tạo booking thất bại']);
        $_SESSION['old_data'] = $data; // Giữ lại dữ liệu đã nhập

        header("Location: index.php?act=admin-booking-create");
        exit;
    }

    /** ------------------------
     *  Form sửa booking (CHỈ CUSTOM REQUEST)
     */
    public function edit(string $act): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $booking = $this->bookingModel->find($id);

        if (!$booking) {
            $_SESSION['error'] = "❌ Booking không tồn tại!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        // ✅ Kiểm tra xem có phải custom request không
        if (empty($booking['is_custom_request'])) {
            $_SESSION['error'] = "⚠️ Chỉ được sửa booking cho tour theo yêu cầu!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        // ✅ Lấy dữ liệu liên quan
        $schedules = $this->bookingModel->getSchedules();
        $items = $this->itemModel->getItemsByBooking($id);
        $statusHistory = $this->bookingModel->getStatusHistory($id);
        $statusText = BookingModel::$statusLabels;

        $pageTitle = "Sửa Booking #" . $booking['booking_code'];
        $currentAct = $act;
        $view = "views/admin/Booking/edit.php";
        include "./views/layout/adminLayout.php";
    }

    /** ------------------------
     *  Xử lý cập nhật booking
     */
    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $author_id = $_SESSION['user_id'] ?? null;

        $data = $_POST;
    
        $res = $this->bookingModel->update($id, $data, $author_id);

        if ($res['ok'] ?? false) {
            // ✅ Xử lý items
            $this->handleBookingItems($id, $data);

            $_SESSION['success'] = "✅ Cập nhật booking thành công!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        // ❌ Xử lý lỗi
        $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Cập nhật thất bại']);
        header("Location: index.php?act=admin-booking-edit&id={$id}");
        exit;
    }

    /** ------------------------
     *  Hủy booking
     */
    public function cancel(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $author_id = $_SESSION['user_id'] ?? null;

        $res = $this->bookingModel->cancelBooking($id, $author_id);

        if ($res['ok'] ?? false) {
            $_SESSION['success'] = "✅ Đã hủy booking thành công!";
        } else {
            $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Hủy booking thất bại']);
        }

        header("Location: index.php?act=admin-booking");
        exit;
    }

    /** ------------------------
     *  Xác nhận booking (PENDING → CONFIRMED)
     */
    public function confirm(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $author_id = $_SESSION['user_id'] ?? null;

        $res = $this->bookingModel->confirmBooking($id, $author_id);

        if ($res['ok'] ?? false) {
            $_SESSION['success'] = "✅ Đã xác nhận booking thành công!";
        } else {
            $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Xác nhận thất bại']);
        }

        header("Location: index.php?act=admin-booking");
        exit;
    }

    /** ------------------------
     *  Xem chi tiết booking
     */
    public function detail(string $act): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $booking = $this->bookingModel->find($id);

        if (!$booking) {
            $_SESSION['error'] = "❌ Booking không tồn tại!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        // ✅ THÊM: Lấy trạng thái thanh toán
        $booking['payment_status'] = $this->bookingModel->getPaymentStatus($id);

        $items = $this->itemModel->getItemsByBooking($id);
        $statusHistory = $this->bookingModel->getStatusHistory($id);
        $statusText = BookingModel::$statusLabels;

        $pageTitle = "Chi tiết Booking #" . $booking['booking_code'];
        $currentAct = $act;
        $view = "views/admin/Booking/detail.php";
        include "./views/layout/adminLayout.php";
    }

    /** ------------------------
     *  Xóa item booking
     */
    public function deleteItem(): void
    {
        $item_id = (int) ($_GET['item_id'] ?? 0);
        $booking_id = (int) ($_GET['booking_id'] ?? 0);

        if (!$item_id || !$booking_id) {
            $_SESSION['error'] = "❌ Dữ liệu không hợp lệ!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        // ✅ Xóa item
        $res = $this->itemModel->deleteItem($item_id);

        if ($res) {
            // ✅ Cập nhật lại tổng tiền booking
            $this->itemModel->updateBookingTotal($booking_id);
            $_SESSION['success'] = "✅ Đã xóa item thành công!";
        } else {
            $_SESSION['error'] = "❌ Xóa item thất bại!";
        }

        header("Location: index.php?act=admin-booking-edit&id={$booking_id}");
        exit;
    }

    /** ------------------------
     *  Helper: Xử lý items (thêm/cập nhật)
     */
    private function handleBookingItems(int $booking_id, array $data): void
    {
        if (empty($data['items']) || !is_array($data['items'])) {
            return;
        }

        foreach ($data['items'] as $item) {
            if (empty($item['description']) || empty($item['qty'])) {
                continue;
            }

            if (!empty($item['id'])) {
                // ✅ Cập nhật item có sẵn
                $this->itemModel->updateItem(
                    (int) $item['id'],
                    $item['description'],
                    (int) $item['qty'],
                    (float) ($item['unit_price'] ?? 0)
                );
            } else {
                // ✅ Thêm item mới
                $this->itemModel->addItem(
                    $booking_id,
                    $item['description'],
                    (int) $item['qty'],
                    (float) ($item['unit_price'] ?? 0),
                    $item['type'] ?? 'SERVICE'
                );
            }
        }

        // ✅ Cập nhật tổng tiền booking sau khi thêm/sửa items
        $this->itemModel->updateBookingTotal($booking_id);
    }

    /** ------------------------
     *  Helper: Format errors thành chuỗi HTML
     */
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

    /** ------------------------
     *  Helper: Validate CSRF token (optional)
     */
    private function validateCsrfToken(string $token): bool
    {
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /** ------------------------
     *  Helper: Generate CSRF token
     */
    public function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}