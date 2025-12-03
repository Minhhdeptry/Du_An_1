<div class="container mt-4">
    <h2 class="mb-3">Thêm Nhân viên</h2>

    <form action="index.php?act=admin-staff-store" method="POST" class="card p-4 shadow-sm">

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
            <input type="text" name="phone" class="form-control" required>
        </div>

        <div class="form-group">
            <label>CMND/CCCD</label>
            <input type="text" name="id_number" class="form-control">
        </div>

        <div class="form-group">
            <label>Trình độ</label>
            <input type="text" name="qualification" class="form-control">
        </div>

        <div class="form-group">
            <label>Trạng thái</label>
            <select name="status" class="form-control">
                <option value="ACTIVE">Đang làm</option>
                <option value="INACTIVE">Nghỉ</option>
            </select>

        </div>

        <button class="btn btn-primary">Thêm mới</button>
        <a href="index.php?act=admin-staff" class="btn btn-secondary">Hủy</a>

    </form>
</div>