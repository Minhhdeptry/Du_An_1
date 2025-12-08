<?php
$currentAct = $currentAct ?? '';
?>

<div class="sidebar">

    <a href="?act=dashboard" class="menu-item <?= ($currentAct == 'dashboard' ? 'active' : '') ?>">
        <i class="fas fa-chart-line menu-icon"></i>
        <span>Dashboard</span>
    </a>

    <a href="?act=admin-tour"
        class="menu-item <?= (in_array($currentAct, ['admin-tour', 'admin-tour-create', 'admin-tour-edit']) ? 'active' : '') ?>">
        <i class="fas fa-map menu-icon"></i>
        <span>Quản lý Danh Sách Tour</span>
    </a>

    <a href="?act=admin-category"
        class="menu-item <?= (in_array($currentAct, ['admin-category', 'admin-category-create', 'admin-category-edit']) ? 'active' : '') ?>">
        <i class="fas fa-list menu-icon"></i>
        <span>Quản lý Danh mục Tour</span>
    </a>

    <a href="?act=admin-booking"
        class="menu-item <?= (in_array($currentAct, ['admin-booking', 'admin-booking-create', 'admin-booking-edit']) ? 'active' : '') ?>">
        <i class="fas fa-book menu-icon"></i>
        <span>Booking</span>
    </a>

    <a href="?act=admin-staff"
        class="menu-item <?= (in_array($currentAct, ['admin-staff', 'admin-staff-create', 'admin-staff-edit']) ? 'active' : '') ?>">
        <i class="fas fa-user-tie menu-icon"></i>
        <span>Nhân viên</span>
    </a>

    <a href="?act=admin-staff-calendar"
        class="menu-item <?= ($currentAct == 'admin-staff-calendar' ? 'active' : '') ?>">
        <i class="fas fa-calendar-alt menu-icon"></i>
        <span>Lịch làm việc HDV</span>
    </a>

    <a href="?act=admin-user"
        class="menu-item <?= (in_array($currentAct, ['admin-user', 'admin-user-create', 'admin-user-edit']) ? 'active' : '') ?>">
        <i class="fas fa-users menu-icon"></i>
        <span>Quản lý khách hàng</span>
    </a>

    <a href="?act=admin-schedule" class="menu-item <?= ($currentAct == 'admin-schedule' ? 'active' : '') ?>">
        <i class="fas fa-calendar-alt menu-icon"></i>
        <span>Lịch điều hành</span>
    </a>

    <a href="?act=admin-payment"
        class="menu-item <?= (in_array($currentAct, ['admin-payment', 'admin-payment-history']) ? 'active' : '') ?>">
        <i class="fas fa-credit-card menu-icon"></i>
        <span>Quản lý thanh toán</span>
    </a>

    <a href="?act=admin-itinerary-list"
        class="menu-item <?= (in_array($currentAct, ['admin-itinerary-list', 'admin-itinerary', 'admin-itinerary-create', 'admin-itinerary-edit']) ? 'active' : '') ?>">
        <i class="fas fa-calendar-week menu-icon"></i>
        <span>Lịch trình theo ngày</span>
    </a>

    <a href="?act=admin-report" class="menu-item <?= ($currentAct == 'admin-report' ? 'active' : '') ?>">
        <i class="fas fa-chart-pie menu-icon"></i>
        <span>Báo cáo</span>
    </a>

</div>