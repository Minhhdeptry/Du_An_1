<div class="dropdown-overlay" id="dropdownOverlay"></div>

<?php if ($_SESSION['user']['role'] === 'admin'): ?>
<div class="header">
  <div class="logo">
    <div class="logo-icon">
      <i class="fa-solid fa-plane"></i>
    </div>
    <h1>FPOLY Quản lý tour</h1>
  </div>

  <div class="header-right">
    <div class="user-info">
      <div class="user-avatar">
        <?= strtoupper(substr($_SESSION['user']['fullName'], 0, 1)); ?>
      </div>
      <div class="user-details">
        <div class="user-name">
          <?= isset($_SESSION['user']['fullName']) ? $_SESSION['user']['fullName'] : 'Admin' ?>
        </div>
        <div class="user-role">
          <?= isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : '' ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php elseif ($_SESSION['user']['role'] === 'guide'): ?>
<div class="header">
  <div class="logo">
    <div class="logo-icon">
      <i class="fa-solid fa-plane"></i>
    </div>
    <h2>HDV - Quản lí Tour du lịch</h2>
  </div>
  <div class="header-right">
    <div class="user-info" id="userInfo">
      <div class="user-avatar">
        <?= strtoupper(substr($_SESSION['user']['fullName'], 0, 1)); ?>
      </div>
      <div class="user-details">
        <div class="user-name">
          <?= isset($_SESSION['user']['fullName']) ? $_SESSION['user']['fullName'] : 'Nguyễn Văn A' ?>
        </div>
        <div class="user-role">Hướng dẫn viên</div>
      </div>
    </div>

    <div class="dropdown-menu" id="dropdownMenu">
      <div class="dropdown-header">
        <div class="dropdown-user-info">
          <div class="dropdown-avatar">
            <?= strtoupper(substr($_SESSION['user']['fullName'], 0, 1)); ?>
          </div>
          <div class="dropdown-user-details">
            <div class="name">
              <?= isset($_SESSION['user']['fullName']) ? $_SESSION['user']['fullName'] : 'Nguyễn Văn A' ?>
            </div>
            <div class="role">Hướng dẫn viên</div>
          </div>
        </div>
      </div>
      <div class="dropdown-body">
        <a href="#" class="dropdown-item">
          <i class="fa-solid fa-user"></i>
          <span>Thông tin cá nhân</span>
        </a>

        <a href="/sign-out" class="dropdown-item logout">
          <i class="fa-solid fa-right-from-bracket"></i>
          <span>Đăng xuất</span>
        </a>
      </div>
    </div>


  </div>
</div>

<?php endif; ?>


<style>
  .header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 70px;
    background-color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    z-index: 1000;
  }

  .logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 22px;
    font-weight: 600;
    color: #333;
  }

  .logo-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #d4660dff 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
  }

  .header-right {
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
  }

  .user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    transition: background-color 0.2s;
  }

  .user-info:hover {
    background-color: #f5f5f5;
  }

  .user-avatar {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #d4660dff 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 16px;
  }

  .user-name {
    font-weight: 600;
    font-size: 14px;
    color: #333;
  }

  .user-role {
    font-size: 12px;
    color: #888;
  }

  .dropdown-menu {
    position: absolute;
    top: 60px;
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 250px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1001;
  }

  .dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }

  .dropdown-header {
    padding: 16px;
    border-bottom: 1px solid #e0e0e0;
  }

  .dropdown-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .dropdown-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #d4660dff 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 18px;
  }

  .dropdown-user-details .name {
    font-weight: 600;
    font-size: 15px;
    color: #333;
    margin-bottom: 2px;
  }

  .dropdown-user-details .role {
    font-size: 13px;
    color: #888;
  }

  .dropdown-body {
    padding: 8px 0;
  }

  .dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #333;
    text-decoration: none;
    transition: background-color 0.2s;
    cursor: pointer;
  }

  .dropdown-item:hover {
    background-color: #f5f5f5;
  }

  .dropdown-item i {
    width: 20px;
    font-size: 16px;
    color: #666;
  }

  .dropdown-item.logout {
    color: #d32f2f;
    border-top: 1px solid #e0e0e0;
    margin-top: 8px;
  }

  .dropdown-item.logout i {
    color: #d32f2f;
  }

  .dropdown-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: transparent;
    display: none;
    z-index: 999;
  }

  .dropdown-overlay.show {
    display: block;
  }
</style>

<script>
  const userInfo = document.getElementById('userInfo');
  const dropdown = document.getElementById('dropdownMenu');
  const overlay = document.getElementById('dropdownOverlay');

  if (userInfo && dropdown && overlay) {
    userInfo.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdown.classList.toggle('show');
      overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', function () {
      dropdown.classList.remove('show');
      overlay.classList.remove('show');
    });

    dropdown.addEventListener('click', function (e) {
      e.stopPropagation();
    });
  }
</script>