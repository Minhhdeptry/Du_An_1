

<div style="background-image: url('assets/images/bg.jpg');
    background-size: cover;
    background-position: center;
    width: 100%;
    height: 100vh;"
    class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center ">
        <div class="card shadow-lg" style="width: 100%; max-width: 500px; border-radius: 20px;">
            <div class="card-body p-5">
                <h2 class="text-center fw-bold mb-3">Đăng Nhập Tài Khoản</h2>
                <p class="text-center text-muted mb-4">Vui lòng sử dụng tài khoản đã được cấp để đăng nhập</p>
                <form method="post" action="index.php?act=sign-in">
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control form-control-lg bg-light border-0" id="email"
                            placeholder="admin@gmail.com" name="email">
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="password" class="form-label fw-semibold mb-0">Mật khẩu</label>
                            <a href="#" class="text-decoration-none text-muted small">Quên mật khẩu?</a>
                        </div>
                        <input type="password" class="form-control form-control-lg bg-light border-0" id="password"
                            placeholder="••••••" name="password">
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label text-muted" for="remember">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>
                   <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php echo $_SESSION['error']; ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

                    <button type="submit" class="btn btn-lg w-100 text-white fw-semibold mb-3"
                        style="background-color: #FF8B6A; border: none; border-radius: 8px;">
                        Sign In
                    </button>

                    <p class="text-center text-muted mb-0">
                        Bạn không có tài khoản?
                        <a href="index.php?act=sign-up" class="text-danger text-decoration-none fw-semibold">Đăng ký ngay</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>