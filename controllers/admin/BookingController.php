<?php
require_once "./models/admin/BookingModel.php";
require_once "./models/admin/BookingItemModel.php";

class BookingController
{
    private $bookingModel;
    private $itemModel;

    public function __construct()
    {
        $pdo = connectDB();
        $this->bookingModel = new BookingModel($pdo);
        $this->itemModel = new BookingItemModel($pdo);
    }

    public function index(string $act): void
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $bookings = $keyword
            ? $this->bookingModel->searchByKeyword($keyword)
            : $this->bookingModel->getAll();
        foreach ($bookings as &$booking) {
            $booking['payment_status'] = $this->bookingModel->getPaymentStatus($booking['id']);
        }

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

    public function createForm(string $act): void
    {
        $schedules = $this->bookingModel->getOpenSchedules();

        if (empty($schedules)) {
            $_SESSION['error'] = "⚠️ Chưa có lịch tour nào đang mở!";
            header("Location: index.php?act=admin-tour");
            exit;
        }

        foreach ($schedules as &$schedule) {
            $schedule_id = $schedule['id'];
            $stmt = $this->bookingModel->getConnection()->prepare("
                SELECT ts.is_custom_request, ts.seats_total, t.is_custom
                FROM tour_schedule ts
                LEFT JOIN tours t ON t.id = ts.tour_id
                WHERE ts.id = ?
            ");
            $stmt->execute([$schedule_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $isScheduleCustom = (int) ($result['is_custom_request'] ?? 0) === 1;
                $isTourCustom = (int) ($result['is_custom'] ?? 0) === 1;
                $seatsTotal = (int) ($result['seats_total'] ?? 0);

                if ($seatsTotal === 0) {
                    $schedule['is_custom_request'] = 1;
                } else {
                    $schedule['is_custom_request'] = ($isScheduleCustom || $isTourCustom) ? 1 : 0;
                }
            }
        }

        $pageTitle = "Tạo Booking theo lịch khởi hành";
        $currentAct = $act;
        $view = "views/admin/Booking/create.php";
        include "./views/layout/adminLayout.php";
    }

    public function store(): void
    {
        $author_id = $_SESSION['user_id'] ?? null;
        $data = $_POST;

        // Kiểm tra capacity cho tour thường
        if (!empty($data['tour_schedule_id'])) {
            $schedule_id = (int) $data['tour_schedule_id'];
            $adults = (int) ($data['adults'] ?? 0);
            $children = (int) ($data['children'] ?? 0);

            if (!$this->bookingModel->checkCapacity($schedule_id, $adults, $children)) {
                $_SESSION['error'] = "❌ Không đủ chỗ! Tour này đã hết chỗ.";
                $_SESSION['old_data'] = $data;
                header("Location: index.php?act=admin-booking-create");
                exit;
            }
        }

        $res = $this->bookingModel->create($data, $author_id);

        if ($res['ok'] ?? false) {
            $booking_id = $res['booking_id'];

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (!empty(trim($item['description'] ?? '')) && !empty($item['qty'])) {
                        $this->itemModel->addItem(
                            $booking_id,
                            trim($item['description']),
                            (int) ($item['qty']),
                            (float) ($item['unit_price'] ?? 0),
                            $item['type'] ?? 'SERVICE'
                        );
                    }
                }
            }

            $this->bookingModel->recalculateTotal($booking_id);

            $booking = $this->bookingModel->find($booking_id);
            $_SESSION['success'] = "✅ Tạo booking thành công! Mã: " . ($booking['booking_code'] ?? 'N/A');
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Tạo booking thất bại']);
        $_SESSION['old_data'] = $data;
        header("Location: index.php?act=admin-booking-create");
        exit;
    }

    public function edit(string $act): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $booking = $this->bookingModel->find($id);

        if (!$booking) {
            $_SESSION['error'] = "❌ Booking không tồn tại!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $booking['payment_status'] = $this->bookingModel->getPaymentStatus($id);
        $schedules = $this->bookingModel->getOpenSchedules();
        $items = $this->itemModel->getItemsByBooking($id);
        $statusHistory = $this->bookingModel->getStatusHistory($id);
        $statusText = BookingModel::$statusLabels;

        $pageTitle = "Sửa Booking #" . $booking['booking_code'];
        $currentAct = $act;
        $view = "views/admin/Booking/edit.php";
        include "./views/layout/adminLayout.php";
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $author_id = $_SESSION['user_id'] ?? null;
        $data = $_POST;

        try {
            $this->handleBookingItems($id, $data);

            $res = $this->bookingModel->update($id, $data, $author_id);

            if ($res['ok'] ?? false) {
                $_SESSION['success'] = "✅ Cập nhật booking thành công!";
                header("Location: index.php?act=admin-booking");
                exit;
            } else {
                $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Cập nhật thất bại']);
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
            error_log("BookingController::update Error: " . $e->getMessage());
        }
        header("Location: index.php?act=admin-booking-edit&id={$id}");
        exit;
    }

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

    public function confirm(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $author_id = $_SESSION['user_id'] ?? null;

        $res = $this->bookingModel->confirmBooking($id, $author_id);

        if ($res['ok'] ?? false) {
            $_SESSION['success'] = "✅ " . ($res['message'] ?? 'Đã xác nhận booking');
        } else {
            $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Xác nhận thất bại']);
        }

        header("Location: index.php?act=admin-booking");
        exit;
    }

    public function startTour(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $author_id = $_SESSION['user_id'] ?? null;

        $res = $this->bookingModel->startTour($id, $author_id);

        if ($res['ok'] ?? false) {
            $_SESSION['success'] = "✅ " . ($res['message'] ?? 'Tour đã bắt đầu');
        } else {
            $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Bắt đầu tour thất bại']);
        }

        header("Location: index.php?act=admin-booking-detail&id={$id}");
        exit;
    }

    public function complete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $author_id = $_SESSION['user_id'] ?? null;

        $res = $this->bookingModel->markAsCompleted($id, $author_id);

        if ($res['ok'] ?? false) {
            $_SESSION['success'] = "✅ " . ($res['message'] ?? 'Tour đã hoàn tất');
        } else {
            $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Hoàn tất thất bại']);
        }

        header("Location: index.php?act=admin-booking");
        exit;
    }

    public function refund(): void
    {
        $id = (int) ($_POST['booking_id'] ?? $_GET['id'] ?? 0);
        $author_id = $_SESSION['user_id'] ?? null;
        $refundAmount = (float) ($_POST['refund_amount'] ?? 0);
        $reason = trim($_POST['refund_reason'] ?? '');

        $res = $this->bookingModel->refund($id, $author_id, $refundAmount, $reason);

        if ($res['ok'] ?? false) {
            $_SESSION['success'] = "✅ " . ($res['message'] ?? 'Đã hoàn tiền thành công');
            header("Location: index.php?act=admin-booking");
        } else {
            $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Hoàn tiền thất bại']);
            header("Location: index.php?act=admin-booking-detail&id={$id}");
        }
        exit;
    }

    public function markReady(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $author_id = $_SESSION['user_id'] ?? null;

        $res = $this->bookingModel->markAsReady($id, $author_id);

        if ($res['ok'] ?? false) {
            $_SESSION['success'] = "✅ " . ($res['message'] ?? 'Đã chuyển sang Sẵn sàng');
        } else {
            $_SESSION['error'] = $this->formatErrors($res['errors'] ?? ['Chuyển trạng thái thất bại']);
        }

        header("Location: index.php?act=admin-booking-detail&id={$id}");
        exit;
    }

    public function detail(string $act): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $booking = $this->bookingModel->find($id);

        if (!$booking) {
            $_SESSION['error'] = "❌ Booking không tồn tại!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        $booking['payment_status'] = $this->bookingModel->getPaymentStatus($id);
        $items = $this->itemModel->getItemsByBooking($id);
        $statusHistory = $this->bookingModel->getStatusHistory($id);
        $statusText = BookingModel::$statusLabels;

        $pageTitle = "Chi tiết Booking #" . $booking['booking_code'];
        $currentAct = $act;
        $view = "views/admin/Booking/detail.php";
        include "./views/layout/adminLayout.php";
    }

    public function deleteItem(): void
    {
        $item_id = (int) ($_GET['item_id'] ?? 0);
        $booking_id = (int) ($_GET['booking_id'] ?? 0);

        if (!$item_id || !$booking_id) {
            $_SESSION['error'] = "❌ Dữ liệu không hợp lệ!";
            header("Location: index.php?act=admin-booking");
            exit;
        }

        try {
            // Xóa item (soft delete) - tự động tính lại tổng tiền
            $res = $this->itemModel->deleteItem($item_id);

            if ($res) {
                $_SESSION['success'] = "✅ Đã xóa dịch vụ thành công!";
            } else {
                $_SESSION['error'] = "❌ Xóa dịch vụ thất bại!";
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
            error_log("BookingController::deleteItem Error: " . $e->getMessage());
        }

        header("Location: index.php?act=admin-booking-edit&id={$booking_id}");
        exit;
    }

    private function handleBookingItems(int $booking_id, array $data): void
    {
        if (empty($data['items']) || !is_array($data['items'])) {
            return;
        }

        foreach ($data['items'] as $item) {
            // Bỏ qua item rỗng
            if (empty(trim($item['description'] ?? '')) || empty($item['qty'])) {
                continue;
            }

            $description = trim($item['description']);
            $qty = (int) $item['qty'];
            $unit_price = (float) ($item['unit_price'] ?? 0);
            $type = $item['type'] ?? 'SERVICE';

            // Validate
            if ($qty <= 0 || $unit_price < 0) {
                continue;
            }

            if (!empty($item['id'])) {
                // ✅ CẬP NHẬT item có sẵn
                $this->itemModel->updateItem((int) $item['id'], $description, $qty, $unit_price);
            } else {
                // ✅ THÊM item mới
                $this->itemModel->addItem($booking_id, $description, $qty, $unit_price, $type);
            }
        }
    }
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

    public function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}