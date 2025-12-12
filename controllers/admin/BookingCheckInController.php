<?php
// controllers/admin/BookingCheckInController.php

require_once "./models/admin/BookingCheckInModel.php";
require_once "./models/admin/BookingModel.php";
require_once "./models/admin/TourScheduleModel.php";

class BookingCheckInController
{
    private $checkInModel;
    private $bookingModel;
    private $scheduleModel;

    public function __construct()
    {
        $this->checkInModel = new BookingCheckInModel();
        $this->bookingModel = new BookingModel();
        $this->scheduleModel = new TourScheduleModel();
    }

    /**
     * ✅ DANH SÁCH TOUR CẦN CHECK-IN HÔM NAY
     */
    public function todayCheckIn($act)
    {
        $date = $_GET['date'] ?? date('Y-m-d');

        // Lấy các tour khởi hành hôm nay
        $schedules = $this->scheduleModel->getByDate($date);

        $tourData = [];
        foreach ($schedules as $schedule) {
            $bookings = $this->bookingModel->getBySchedule($schedule['id']);
            $checkInStats = $this->checkInModel->getCheckInStats($schedule['id']);

            $tourData[] = [
                'schedule' => $schedule,
                'bookings' => $bookings,
                'stats' => $checkInStats
            ];
        }

        $pageTitle = "Check-in Tour - " . date('d/m/Y', strtotime($date));
        $currentAct = $act;
        $view = "./views/admin/CheckIn/today.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * ✅ FORM CHECK-IN CHO 1 BOOKING
     */
    public function checkInForm($act)
    {
        $booking_id = $_GET['booking_id'] ?? null;

        if (!$booking_id) {
            $_SESSION['error'] = "❌ Booking ID không hợp lệ!";
            header("Location: index.php?act=admin-checkin-today");
            exit;
        }

        $booking = $this->bookingModel->find($booking_id);
        if (!$booking) {
            $_SESSION['error'] = "❌ Booking không tồn tại!";
            header("Location: index.php?act=admin-checkin-today");
            exit;
        }

        // Lấy lịch sử check-in (nếu đã check-in rồi)
        $checkInRecord = $this->checkInModel->getByBooking($booking_id);

        $pageTitle = "Check-in - Booking #" . $booking['booking_code'];
        $currentAct = $act;
        $view = "./views/admin/CheckIn/form.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * ✅ XỬ LÝ CHECK-IN
     */
    public function processCheckIn()
    {
        $booking_id = $_POST['booking_id'] ?? null;
        $status = $_POST['status'] ?? 'PRESENT'; // PRESENT, ABSENT, LATE
        $note = $_POST['note'] ?? '';
        $admin_id = $_SESSION['user_id'] ?? null;

        if (!$booking_id) {
            $_SESSION['error'] = "❌ Thiếu thông tin booking!";
            header("Location: index.php?act=admin-checkin-today");
            exit;
        }

        try {
            $result = $this->checkInModel->checkIn([
                'booking_id' => $booking_id,
                'checked_in_by' => $admin_id,
                'status' => $status,
                'note' => $note,
                'check_in_time' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                $_SESSION['success'] = "✅ Check-in thành công!";
            } else {
                $_SESSION['error'] = "❌ Check-in thất bại!";
            }

        } catch (Exception $e) {
            $_SESSION['error'] = "❌ Lỗi: " . $e->getMessage();
        }

        $booking = $this->bookingModel->find($booking_id);
        header("Location: index.php?act=admin-checkin-today&date=" . ($booking['depart_date'] ?? date('Y-m-d')));
        exit;
    }

    /**
     * ✅ BÁO CÁO CHECK-IN THEO TOUR
     */
    public function reportByTour($act)
    {
        $schedule_id = $_GET['schedule_id'] ?? null;

        if (!$schedule_id) {
            $_SESSION['error'] = "❌ Tour không hợp lệ!";
            header("Location: index.php?act=admin-checkin-today");
            exit;
        }

        $schedule = $this->scheduleModel->find($schedule_id);
        $bookings = $this->bookingModel->getBySchedule($schedule_id);
        $checkInData = [];

        foreach ($bookings as $booking) {
            $checkInRecord = $this->checkInModel->getByBooking($booking['id']);
            $checkInData[] = [
                'booking' => $booking,
                'check_in' => $checkInRecord
            ];
        }

        $stats = $this->checkInModel->getCheckInStats($schedule_id);

        $pageTitle = "Báo cáo Check-in - " . ($schedule['tour_title'] ?? 'Tour');
        $currentAct = $act;
        $view = "./views/admin/CheckIn/report.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * ✅ HỦY CHECK-IN
     */
    public function undoCheckIn()
    {
        $booking_id = $_GET['booking_id'] ?? null;

        if (!$booking_id) {
            $_SESSION['error'] = "❌ Booking ID không hợp lệ!";
            header("Location: index.php?act=admin-checkin-today");
            exit;
        }

        $result = $this->checkInModel->undoCheckIn($booking_id);

        if ($result) {
            $_SESSION['success'] = "✅ Đã hủy check-in!";
        } else {
            $_SESSION['error'] = "❌ Hủy check-in thất bại!";
        }

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?act=admin-checkin-today'));
        exit;
    }

    /**
     * ✅ DANH SÁCH TẤT CẢ CHECK-IN (Lọc theo ngày)
     */
    public function history($act)
    {
        $from_date = $_GET['from_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $to_date = $_GET['to_date'] ?? date('Y-m-d');
        $status = $_GET['status'] ?? '';

        $checkIns = $this->checkInModel->getCheckInHistory($from_date, $to_date, $status);

        $pageTitle = "Lịch sử Check-in";
        $currentAct = $act;
        $view = "./views/admin/CheckIn/history.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * ✅ CHECK-IN CHI TIẾT TỪNG KHÁCH
     */
    public function checkInGuests($act)
    {
        $booking_id = $_GET['booking_id'] ?? null;

        if (!$booking_id) {
            $_SESSION['error'] = "❌ Booking ID không hợp lệ!";
            header("Location: index.php?act=admin-checkin-today");
            exit;
        }

        // Lấy thông tin booking và danh sách khách đã check-in
        $bookingInfo = $this->checkInModel->getBookingGuests($booking_id);
        $checkedInGuests = $this->checkInModel->getCheckInList($booking_id);

        $pageTitle = "Check-in Khách - " . $bookingInfo['booking_code'];
        $currentAct = $act;
        $view = "./views/admin/CheckIn/guests.php";
        include "./views/layout/adminLayout.php";
    }

    /**
     * ✅ XỬ LÝ CHECK-IN TỪNG KHÁCH
     */
    public function processGuestCheckIn()
    {
        $data = [
            'booking_id' => $_POST['booking_id'] ?? null,
            'guest_name' => $_POST['guest_name'] ?? '',
            'guest_type' => $_POST['guest_type'] ?? 'ADULT',
            'id_number' => $_POST['id_number'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'check_in_status' => $_POST['check_in_status'] ?? 'PRESENT',
            'notes' => $_POST['notes'] ?? null
        ];

        $result = $this->checkInModel->checkInGuest($data);

        if ($result['ok']) {
            $_SESSION['success'] = "✅ Check-in khách thành công!";
        } else {
            $_SESSION['error'] = "❌ " . implode(", ", $result['errors']);
        }

        header("Location: index.php?act=admin-checkin-guests&booking_id=" . $data['booking_id']);
        exit;
    }

    /**
     * ✅ CHECK-IN NHANH TOÀN BỘ BOOKING
     */
    public function quickCheckInAll()
    {
        $booking_id = $_POST['booking_id'] ?? null;

        if (!$booking_id) {
            $_SESSION['error'] = "❌ Booking ID không hợp lệ!";
            header("Location: index.php?act=admin-checkin-today");
            exit;
        }

        $result = $this->checkInModel->quickCheckInAll($booking_id);

        if ($result['ok']) {
            $_SESSION['success'] = "✅ Check-in nhanh thành công toàn bộ khách!";
        } else {
            $_SESSION['error'] = "❌ " . implode(", ", $result['errors']);
        }

        header("Location: index.php?act=admin-checkin-guests&booking_id=" . $booking_id);
        exit;
    }

    /**
     * ✅ CẬP NHẬT TRẠNG THÁI CHECK-IN
     */
    public function updateCheckInStatus()
    {
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? 'PRESENT';
        $notes = $_POST['notes'] ?? null;

        if (!$id) {
            echo json_encode(['ok' => false, 'error' => 'ID không hợp lệ']);
            exit;
        }

        $result = $this->checkInModel->updateCheckInStatus($id, $status, $notes);
        echo json_encode($result);
        exit;
    }

    /**
     * ✅ XÓA CHECK-IN (nếu nhầm)
     */
    public function deleteCheckIn()
    {
        $id = $_GET['id'] ?? null;
        $booking_id = $_GET['booking_id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = "❌ ID không hợp lệ!";
            header("Location: index.php?act=admin-checkin-today");
            exit;
        }

        $result = $this->checkInModel->deleteCheckIn($id);

        if ($result['ok']) {
            $_SESSION['success'] = "✅ Đã xóa check-in!";
        } else {
            $_SESSION['error'] = "❌ " . implode(", ", $result['errors']);
        }

        header("Location: index.php?act=admin-checkin-guests&booking_id=" . $booking_id);
        exit;
    }
}