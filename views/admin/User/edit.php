<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">✏️ Sửa Khách hàng</h2>
        <a href="index.php?act=admin-user" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <form action="index.php?act=admin-user-update" method="POST" class="card p-4 shadow-sm">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">

        <div class="form-group">
            <label>Họ tên</label>
            <input type="text" name="full_name" class="form-control" required
                   value="<?= htmlspecialchars($row['full_name']) ?>">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($row['email']) ?>">
        </div>

        <div class="form-group">
            <label>Số điện thoại</label>
            <input type="text" name="phone" class="form-control" required
                   value="<?= htmlspecialchars($row['phone']) ?>">
        </div>

        <div class="form-group form-check">
            <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                <?= !empty($row['is_active']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">Kích hoạt</label>
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="index.php?act=admin-user" class="btn btn-secondary">Hủy</a>

    </form>
</div>
