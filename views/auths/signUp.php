<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


<div style="   background-image: url('assets/images/bg.jpg');
    background-size: cover;
    background-position: center;
    width: 100%;
    height: 100vh;"
    
    class="d-flex align-items-center justify-content-center min-vh-100">

    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-3 ">
        <div class="card shadow-lg px-3" style="width: 100%; max-width: 500px; border-radius: 20px;">
            <div class="card-body p-3">
                <h2 class="text-center fw-bold mb-2">Đăng Ký Tài Khoản</h2>
                <p class="text-center text-muted mb-3">Điền thông tin để tạo tài khoản mới</p>
                <form method="POST" action="sign-up">
                    <div class="mb-2">
                        <label for="fullname" class="form-label fw-semibold">Họ và tên</label>
                        <input type="text" class="form-control form-control-lg bg-light border-0" id="fullname"
                            name="fullName" placeholder="">
                        <?php if (isset($_SESSION['errorFullname'])): ?>
                            <?php echo $_SESSION['errorFullname']; ?>
                            <?php unset($_SESSION['errorFullname']); ?>
                        <?php endif; ?>
                    </div>



                    <div class="mb-2">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control form-control-lg bg-light border-0" id="email"
                            name="email" placeholder="">
                        <?php if (isset($_SESSION['errorEmail'])): ?>
                            <?php echo $_SESSION['errorEmail']; ?>
                            <?php unset($_SESSION['errorEmail']); ?>
                        <?php endif; ?>
                    </div>

                    <div class="mb-2">
                        <label for="password" class="form-label fw-semibold">Mật khẩu</label>
                        <input type="password" class="form-control form-control-lg bg-light border-0" id="password"
                            name="password" placeholder="">
                        <?php if (isset($_SESSION['errorPassword'])): ?>
                            <?php echo $_SESSION['errorPassword']; ?>
                            <?php unset($_SESSION['errorPassword']); ?>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label fw-semibold">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control form-control-lg bg-light border-0"
                            name="confirmPassword" id="confirmPassword" placeholder="">
                        <span style="color: red;">
                            <?php if (isset($_SESSION['errorConfirmPassword'])): ?>
                                <?php echo $_SESSION['errorConfirmPassword']; ?>
                                <?php unset($_SESSION['errorConfirmPassword']); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if (isset($_SESSION['error'])): ?>
                        <?php echo $_SESSION['error']; ?>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    <button class="btn btn-lg w-100 text-white fw-semibold my-3"
                        style="background-color: #FF8B6A; border: none; border-radius: 8px;">
                        Đăng Ký
                    </button>

                    <p class="text-center text-muted mb-0">
                        Đã có tài khoản?
                        <a href="sign-in" class="text-danger text-decoration-none fw-semibold">Đăng nhập ngay</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- <?php if (isset($_SESSION['success'])): ?>
    <script>
        var myModal = new bootstrap.Modal(document.getElementById('successModal'));
        myModal.show();
    </script>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <script>
        var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
        myModal.show();
    </script>
<?php endif; ?> -->