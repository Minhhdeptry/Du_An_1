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

    <a href="?act=admin-booking" 
       class="menu-item <?= (in_array($currentAct, ['admin-booking', 'admin-booking-create', 'admin-booking-edit']) ? 'active' : '') ?>">
        <span class="menu-icon icon-booking"></span>
        <span>Booking</span>
    </a>

    <a href="?act=staffs" 
       class="menu-item <?= (in_array($currentAct, ['admin-staff', 'admin-staff-create', 'admin-staff-edit']) ? 'active' : '') ?>">
        <span class="menu-icon icon-guide"></span>
        <span>Hướng dẫn viên</span>
    </a>

    <a href="?act=schedules" 
       class="menu-item <?= ($currentAct == 'schedules' ? 'active' : '') ?>">
        <span class="menu-icon icon-schedule"></span>
        <span>Lịch điều hành</span>
    </a>

    <a href="?act=reports" 
       class="menu-item <?= ($currentAct == 'reports' ? 'active' : '') ?>">
        <span class="menu-icon icon-report"></span>
        <span>Báo cáo</span>
    </a>

</div>