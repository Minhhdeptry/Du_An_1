<?php 
$currentAct = $currentAct ?? '';
?>

<div class="sidebar">

    <a href="?act=dashboard"
       class="menu-item <?= ($currentAct == 'dashboard' ? 'active' : '') ?>">
        <span class="menu-icon icon-dashboard"></span>
        <span>Dashboard</span>
    </a>

    <a href="?act=admin-tour" 
       class="menu-item <?= (in_array($currentAct, ['admin-tour', 'admin-tour-create', 'admin-tour-edit']) ? 'active' : '') ?>">
        <span class="menu-icon icon-tour"></span>
        <span>Quản lý Danh Sách Tour</span>
    </a>

    <a href="?act=admin-category" 
       class="menu-item <?= (in_array($currentAct, ['admin-category', 'admin-category-create', 'admin-category-edit']) ? 'active' : '') ?>">
        <span class="menu-icon icon-tour"></span>
        <span>Quản lý Danh mục tour</span>
    </a>

    <a href="?act=admin-booking" 
       class="menu-item <?= (in_array($currentAct, ['admin-booking', 'admin-booking-create', 'admin-booking-edit']) ? 'active' : '') ?>">
        <span class="menu-icon icon-booking"></span>
        <span>Booking</span>
    </a>

    <a href="?act=admin-staff" 
       class="menu-item <?= (in_array($currentAct, ['admin-staff', 'admin-staff-create', 'admin-staff-edit']) ? 'active' : '') ?>">
        <span class="menu-icon icon-guide"></span>
        <span>Nhân viên</span>
    </a>

    <a href="?act=admin-user" 
       class="menu-item <?= (in_array($currentAct, ['admin-user', 'admin-user-create', 'admin-user-edit']) ? 'active' : '') ?>">
        <span class="menu-icon icon-user"></span>
        <span>Quản lý khách hàng</span>
    </a>

    <a href="?act=admin-schedule" 
       class="menu-item <?= ($currentAct == 'schedule' ? 'active' : '') ?>">
        <span class="menu-icon icon-schedule"></span>
        <span>Lịch điều hành</span>
    </a>

    <a href="?act=admin-reports" 
       class="menu-item <?= ($currentAct == 'reports' ? 'active' : '') ?>">
        <span class="menu-icon icon-report"></span>
        <span>Báo cáo</span>
    </a>

</div>