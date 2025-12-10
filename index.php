<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
  integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
  integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
  integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
  integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<?php
session_start();

// ✅ Bước 2: Load môi trường và function
require_once './commons/env.php'; // Khai báo biến môi trường
require_once './commons/function.php'; // Hàm hỗ trợ

// Controllers
require_once './controllers/admin/DashboardController.php';
require_once './controllers/admin/TourController.php';
require_once './controllers/admin/BookingController.php';
require_once './controllers/admin/CategoryController.php';
require_once './controllers/admin/ScheduleController.php';
require_once './controllers/admin/StaffController.php';
require_once './controllers/admin/UserController.php';
require_once './controllers/admin/PaymentController.php';
require_once './controllers/admin/ReportController.php';
require_once './controllers/admin/ItineraryController.php';

require_once './controllers/admin/StaffScheduleController.php';
require_once './controllers/admin/StaffCertificateController.php';
require_once './controllers/admin/StaffRatingController.php';


// Auth
require_once './controllers/admin/AuthController.php';

// Route
$act = ($_GET['act'] ?? 'dashboard');
$currentAct = $act;

// ✅ Bước 5: Middleware - Kiểm tra đăng nhập
$publicRoutes = ['sign-in', 'sign-up', 'logout'];

if (!in_array($act, $publicRoutes)) {
    // Kiểm tra đã đăng nhập chưa
    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = "Vui lòng đăng nhập để tiếp tục!";
        header("Location: index.php?act=sign-in");
        exit();
    }

    // ✅ FIX: Chỉ kiểm tra role ADMIN cho các route có prefix "admin-"
    if (strpos($act, 'admin-') === 0 && $_SESSION['user']['role'] !== 'ADMIN') {
        $_SESSION['error'] = "Bạn không có quyền truy cập!";
        header("Location: index.php?act=sign-in");
        exit();
    }
}

match ($act) {

  // ================= AUTH ===================
  'sign-in' => (new AuthController())->SignIn(),
  'sign-up' => (new AuthController())->SignUp(),
  'logout' => (new AuthController())->logout(),


  // ================= TOUR ADMIN ===================
  'admin-tour' => (new TourController())->index($currentAct),
  'admin-tour-create' => (new TourController())->create($currentAct),
  'admin-tour-store' => (new TourController())->store(),
  'admin-tour-edit' => (new TourController())->edit($currentAct),
  'admin-tour-detail' => (new TourController())->detail($currentAct),
  'admin-tour-update' => (new TourController())->update(),
  'admin-tour-delete' => (new TourController())->delete(),
  

  // ================= BOOKING ADMIN ===================
  'admin-booking' => (new BookingController())->index($currentAct),
  'admin-booking-edit' => (new BookingController())->edit($currentAct),
  'admin-booking-update' => (new BookingController())->update(),
  'admin-booking-cancel' => (new BookingController())->cancel(),
  'admin-booking-create' => (new BookingController())->createForm($currentAct),
  'admin-booking-store' => (new BookingController())->store(),
  'admin-booking-confirm' => (new BookingController())->confirm(),
  'admin-booking-detail' => (new BookingController())->detail($currentAct),
  // Xóa item dùng deleteItem()
  'admin-booking-item-delete' => (new BookingController())->deleteItem(),


  // ================= CATEGORY ADMIN ===================
  'admin-category' => (new CategoryController())->index($currentAct),
  'admin-category-create' => (new CategoryController())->create($currentAct),
  'admin-category-store' => (new CategoryController())->store(),
  'admin-category-edit' => (new CategoryController())->edit($currentAct),
  'admin-category-update' => (new CategoryController())->update(),
  'admin-category-delete' => (new CategoryController())->delete(),

  // Lịch làm việc HDV
    'admin-staff-calendar' => (new StaffScheduleController())->calendar($currentAct),
    
  // Phân công HDV cho tour
    'admin-staff-assign-form' => (new StaffScheduleController())->assignForm($currentAct),
    'admin-staff-assign-store' => (new StaffScheduleController())->assignStore(),
    'admin-staff-remove-assignment' => (new StaffScheduleController())->removeAssignment(),
    'admin-staff-remove-guide' => (new StaffScheduleController())->removeGuide(),
    
  // Hiệu suất HDV
    'admin-staff-performance' => (new StaffScheduleController())->performance($currentAct),
    
  // API check availability
    'admin-staff-check-availability' => (new StaffScheduleController())->checkAvailability(),
    
  // ================= ĐÁNH GIÁ HDV ===================
    
  // Danh sách đánh giá
    'admin-staff-rating' => (new StaffRatingController())->index($currentAct),
    
  // Thêm đánh giá
    'admin-staff-rating-create' => (new StaffRatingController())->create($currentAct),
    'admin-staff-rating-store' => (new StaffRatingController())->store(),

  // ================= SCHEDULE ADMIN ===================
  'admin-schedule' => (new ScheduleController())->index($currentAct),
  'admin-schedule-create' => (new ScheduleController())->create($currentAct),
  'admin-schedule-store' => (new ScheduleController())->store(),
  'admin-schedule-edit' => (new ScheduleController())->edit($currentAct),
  'admin-schedule-update' => (new ScheduleController())->update(),
  'admin-schedule-delete' => (new ScheduleController())->delete(),

  // ================= STAFF ADMIN ===================
  'admin-staff' => (new StaffController())->index($currentAct),
  'admin-staff-create' => (new StaffController())->create($currentAct),
  'admin-staff-edit' => (new StaffController())->edit($currentAct),
  'admin-staff-detail' => (new StaffController())->detail($currentAct),
  'admin-staff-store' => (new StaffController())->store(),
  'admin-staff-update' => (new StaffController())->update(),
  'admin-staff-delete' => (new StaffController())->delete(),

  // ================= USER ADMIN ===================
  'admin-user' => (new UserController())->index($currentAct),
  'admin-user-create' => (new UserController())->create($currentAct),
  'admin-user-edit' => (new UserController())->edit($currentAct),
  'admin-user-update' => (new UserController())->update(),
  'admin-user-store' => (new UserController())->store(),
  'admin-user-delete' => (new UserController())->delete(),
  'admin-user-history' => (new UserController())->history($currentAct),

  // ================= PAYMENT ADMIN ===================
  'admin-payment' => (new PaymentController())->index($currentAct),
  'admin-payment-history' => (new PaymentController())->history($currentAct),
  'admin-payment-edit' => (new PaymentController())->editForm($currentAct),
  'admin-payment-confirm' => (new PaymentController())->confirm(),
  'admin-payment-cancel' => (new PaymentController())->cancel(),
  'admin-payment-create' => (new PaymentController())->createForm($currentAct),
  'admin-payment-store' => (new PaymentController())->store(),
  'admin-payment-update' => (new PaymentController())->update(),


  // ================= REPORT ===================
  'admin-report' => (new ReportController())->index($currentAct),

  // ================= ITINERARY (Lịch trình Tour) ===================
  'admin-itinerary-list' => (new ItineraryController())->selectTour($currentAct), 
  'admin-itinerary' => (new ItineraryController())->index($currentAct),
  'admin-itinerary-create' => (new ItineraryController())->create($currentAct),
  'admin-itinerary-store' => (new ItineraryController())->store(),
  'admin-itinerary-edit' => (new ItineraryController())->edit($currentAct),
  'admin-itinerary-update' => (new ItineraryController())->update(),
  'admin-itinerary-delete' => (new ItineraryController())->delete(),



  // ================= DASHBOARD ===================
  'dashboard' => (new DashboardController())->index($currentAct),


  // ================= 404 ===================
  default => include './views/errorPage.php',
};

