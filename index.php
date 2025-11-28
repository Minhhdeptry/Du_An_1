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


  // Categories
  'categories' => (new AuthController())->SignUP(),



  

// ================= DASHBOARD ===================
  'dashboard'                 => (new DashboardController())->index($currentAct),


// ================= 404 ===================
  default => include './views/errorPage.php',
};

