<div class="header d-flex justify-content-between align-items-center px-3 py-2">

    <div class="logo d-flex align-items-center">
        <div class="logo-icon">📍</div>
        <span class="ml-2">TourManager Pro</span>
    </div>

    <div class="user-section d-flex align-items-center">
        <?php // Debug: print session role and full_name to browser console (dev only) 
        ?>
        <?php if (isset($_SESSION)) :
            $logRole = $_SESSION['role'] ?? '';
            $logName = $_SESSION['user']['full_name'] ?? ($_SESSION['full_name'] ?? '');
        ?>
            <script>
                console.log('session role:', <?= json_encode($logRole) ?>);
                console.log('session full_name:', <?= json_encode($logName) ?>);
            </script>
        <?php endif; ?>
        <div class="user-avatar d-flex align-items-center mr-3">
            <div class="avatar bg-primary text-white rounded-circle p-2 mr-2">
                <?php
                $avatarName = $_SESSION['user']['full_name'] ?? ($_SESSION['full_name'] ?? 'A');
                echo strtoupper(mb_substr($avatarName, 0, 1, 'UTF-8'));
                ?>
            </div>
            <div class="user-info">
                <div class="user-name">Xin chào! <?= htmlspecialchars($_SESSION['user']['full_name'] ?? ($_SESSION['full_name'] ?? 'Admin User')) ?></div>
                <div class="user-role">
                    <?php
                    $roleLabels = [
                        'ADMIN' => 'Quản trị viên',
                        'HDV' => 'Hướng dẫn viên',
                        'CUSTOMER' => 'Khách hàng'
                    ];
                    echo $roleLabels[$_SESSION['role'] ?? 'ADMIN'] ?? 'User';
                    ?>
                </div>
            </div>
        </div>

        <!-- Nút đăng xuất -->
        <a href="index.php?act=logout" class="btn btn-sm btn-outline-danger"
            onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>

</div>

<style>
    .btn-outline-danger {
        border: 1px solid #dc3545;
        color: #dc3545;
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        transition: 0.3s;
    }

    .btn-outline-danger:hover {
        background: #dc3545;
        color: white;
    }
</style>