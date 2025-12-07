<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">➕ Thêm mới Nhân viên</h2>
        <a href="index.php?act=admin-staff" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

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

        <!-- Ngày sinh -->
        <div class="form-group">
            <label>Ngày sinh</label>
            <input type="date" name="date_of_birth" class="form-control">
        </div>

        <!-- Ảnh đại diện -->
        <div class="form-group">
            <label>Ảnh đại diện</label>
            <input type="file" name="profile_image" class="form-control">
        </div>

        <!-- Chứng chỉ -->
        <div class="form-group">
            <label>Chứng chỉ chuyên môn</label>
            <textarea name="certifications" class="form-control"
                placeholder="VD: Hướng dẫn viên du lịch quốc gia"></textarea>
        </div>

        <!-- Ngôn ngữ -->
        <div class="form-group">
            <label>Ngôn ngữ sử dụng</label>
            <input type="text" name="languages" class="form-control" placeholder="VD: Tiếng Anh, Tiếng Pháp">
        </div>

        <!-- Kinh nghiệm -->
        <div class="form-group">
            <label>Số năm kinh nghiệm</label>
            <input type="number" name="experience_years" class="form-control" min="0">
        </div>

        <!-- Phân loại -->
        <div class="form-group">
            <label>Phân loại HDV</label>
            <select name="staff_type" class="form-control">
                <option value="DOMESTIC">Nội địa</option>
                <option value="INTERNATIONAL">Quốc tế</option>
                <option value="SPECIALIZED">Chuyên tuyến</option>
                <option value="GROUP_TOUR">Chuyên khách đoàn</option>
            </select>
        </div>

        <!-- Đánh giá -->
        <div class="form-group">
            <label>Đánh giá năng lực (0-5)</label>
            <input type="number" name="rating" class="form-control" min="0" max="5" step="0.1">
        </div>

        <!-- Sức khoẻ -->
        <div class="form-group">
            <label>Tình trạng sức khoẻ</label>
            <select name="health_status" class="form-control">
                <option value="good">Tốt</option>
                <option value="fair">Trung bình</option>
                <option value="poor">Yếu</option>
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