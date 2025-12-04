<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">➕ Thêm Khách hàng</h2>
        <a href="index.php?act=admin-user" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <form action="index.php?act=admin-user-store" method="POST" class="card p-4 shadow-sm">

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Họ tên</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div class="form-group">
            <label>Số điện thoại</label>
            <input type="text" name="phone" class="form-control" required>
        </div>

        <div class="form-group form-check">
            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" checked>
            <label class="form-check-label" for="is_active">Kích hoạt</label>
        </div>

        <button type="submit" class="btn btn-primary">Thêm mới</button>
        <a href="index.php?act=admin-user" class="btn btn-secondary">Hủy</a>

    </form>
</div>
