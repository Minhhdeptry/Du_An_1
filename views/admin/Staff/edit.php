<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">✏️ Sửa Nhân viên</h2>
        <a href="index.php?act=admin-staff" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <form action="index.php?act=admin-staff-update" method="POST" class="card p-4 shadow-sm">
        <input type="hidden" name="id" value="<?= $staff['id'] ?>">

        <div class="form-group">
            <label>Tài khoản (User)</label>
            <select name="user_id" class="form-control" required>
                <option value="">-- Chọn user --</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= isset($staff) && $staff['user_id'] == $u['id'] ? 'selected' : '' ?>>
                        <?= $u['full_name'] ?> (<?= $u['email'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>


        <div class="form-group">
            <label>SĐT</label>
            <input type="text" name="phone" class="form-control" value="<?= $staff['phone'] ?>" required>
        </div>

        <div class="form-group">
            <label>CMND/CCCD</label>
            <input type="text" name="id_number" class="form-control" value="<?= $staff['id_number'] ?>">
        </div>

        <div class="form-group">
            <label>Trình độ</label>
            <input type="text" name="qualification" class="form-control" value="<?= $staff['qualification'] ?>">
        </div>

        <div class="form-group">
            <label>Trạng thái</label>
            <select name="status" class="form-control">
                <option value="active" <?= $staff['status'] == 'active' ? 'selected' : '' ?>>Đang làm</option>
                <option value="inactive" <?= $staff['status'] == 'inactive' ? 'selected' : '' ?>>Nghỉ</option>
            </select>
        </div>

        <button class="btn btn-primary">Cập nhật</button>
        <a href="index.php?act=admin-staff" class="btn btn-secondary">Hủy</a>

    </form>
</div>