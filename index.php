<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
  integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
  integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
  integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
  integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<?php
// session_start();


require_once './commons/env.php'; // Khai báo biến môi trường
require_once './commons/function.php'; // Hàm hỗ trợ

// Controllers
require_once './controllers/DashboardController.php';
require_once './controllers/TourController.php';
require_once './controllers/BookingController.php';
require_once './controllers/CategoryController.php';
require_once './controllers/ScheduleController.php';
require_once './controllers/StaffController.php';
require_once './controllers/UserController.php';


// Auth
require_once './controllers/AuthController.php';

// Route
$act = ($_GET['act'] ?? 'dashboard');
$currentAct = $act;

match ($act) {

// ================= AUTH ===================
    'sign-in'           => (new AuthController())->SignIn(),
    'sign-up'           => (new AuthController())->SignUp(),


// ================= TOUR ADMIN ===================
    'admin-tour'        => (new TourController())->index($currentAct),
    'admin-tour-create'    => (new TourController())->create($currentAct),
    'admin-tour-store'  => (new TourController())->store(),
    'admin-tour-edit'   => (new TourController())->edit($currentAct),
    'admin-tour-update' => (new TourController())->update(),
    'admin-tour-delete' => (new TourController())->delete(),

// ================= BOOKING ADMIN ===================
    'admin-booking'     => (new BookingController())->index($currentAct),
    'admin-booking-edit'     => (new BookingController())->edit($currentAct),
    'admin-booking-update'     => (new BookingController())->update(),
    'admin-booking-delete'     => (new BookingController())->delete(),

// ================= CATEGORY ADMIN ===================
    'admin-category'    => (new CategoryController())->index($currentAct),
    'admin-category-create'    => (new CategoryController())->create($currentAct),
    'admin-category-store'    => (new CategoryController())->store(),
    'admin-category-edit'    => (new CategoryController())->edit($currentAct),
    'admin-category-update'    => (new CategoryController())->update(),
    'admin-category-delete'    => (new CategoryController())->delete(),

// ================= SCHEDULE ADMIN ===================
    'admin-schedule'    => (new ScheduleController())->index($currentAct),
    'admin-schedule-create'    => (new ScheduleController())->create($currentAct),
    'admin-schedule-store'    => (new ScheduleController())->store(),
    'admin-schedule-edit'    => (new ScheduleController())->edit($currentAct),
    'admin-schedule-update'    => (new ScheduleController())->update(),
    'admin-schedule-delete'    => (new ScheduleController())->delete(),

// ================= STAFF ADMIN ===================
    'admin-staff'    => (new StaffController())->index($currentAct),
    'admin-staff-create'    => (new StaffController())->create($currentAct),
    'admin-staff-edit'    => (new StaffController())->edit($currentAct),
    'admin-staff-store'    => (new StaffController())->store(),
    'admin-staff-update'    => (new StaffController())->update(),
    'admin-staff-delete'    => (new StaffController())->delete(),

// ================= USER ADMIN ===================
    'admin-user'    => (new UserController())->index($currentAct),
    'admin-user-create'    => (new UserController())->create($currentAct),
    'admin-user-edit'    => (new UserController())->edit($currentAct),
    'admin-user-update'    => (new UserController())->update(),
    'admin-user-store'    => (new UserController())->store(),
    'admin-user-delete'    => (new UserController())->delete(),
    'admin-user-history'    => (new UserController())->history($currentAct),

// ================= USER ADMIN ===================
    // 'admin-user'    => (new ScheduleController())->index($currentAct),





  

// ================= DASHBOARD ===================
  'dashboard'                 => (new DashboardController())->index($currentAct),


// ================= 404 ===================
  default => include './views/errorPage.php',
};

