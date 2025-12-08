<?php if ($_SESSION['user']['role'] === 'admin'): ?>
  <div class="sidebar">
    <a href="/dashboard" class="menu-item <?= $active == 'categories' ? 'active' : '' ?>">
      <i class="fa-solid fa-gauge-high"></i>
      <span>Tổng quan</span>
    </a>
    <a href="/dashboard/categories-manager" class="menu-item <?= $active == 'categories' ? 'active' : '' ?>">
      <i class="fa-regular fa-rectangle-list"></i>
      <span>Quản lí danh mục Tour</span>
    </a>

    <a href="/dashboard/tours-manager" class="menu-item <?= $active == 'guides' ? 'active' : '' ?>">
      <i class="fa-solid fa-route"></i>
      <span>Quản lý Tour</span>
    </a>

    <a href="/dashboard/booking-manager" class="menu-item <?= $active == 'booking' ? 'active' : '' ?>">
      <i class="fa-regular fa-calendar"></i>
      <span>Quản lí Booking</span>
    </a>

    <a href="/dashboard/guide-manager" class="menu-item <?= $active == 'users' ? 'active' : '' ?>">
      <i class="fa-regular fa-circle-user"></i>
      <span>Quản lí hướng dẫn viên</span>
    </a>

    <a href="#" class="menu-item <?= $active == 'statistic' ? 'active' : '' ?>">
      <i class="fa-solid fa-chart-line"></i>
      <span>Báo cáo - Thống kê</span>
    </a>

    <a href="/sign-out" class="menu-item">
      <i class="fa-solid fa-arrow-right-from-bracket"></i>
      <span>Đăng Xuất</span>
    </a>
  </div>

<?php endif; ?>

<style>
  .sidebar {
    position: fixed;
    left: 0;
    top: 70px;
    bottom: 0;
    width: 220px;
    background-color: white;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    padding: 20px 0;
    overflow-y: auto;
  }

  .menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 30px;
    color: #666;
    text-decoration: none;
    font-size: 15px;
    transition: 0.2s;
  }

  .menu-item:hover {
    background-color: #f8f9fa;
    color: #333;
  }

  .menu-item.active {
    background-color: #d4660dff;
    color: white;
    padding-left: 20px;
    border-radius: 8px;
    margin: 0 12px;
  }
</style>