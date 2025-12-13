<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">➕ Thêm mới Hướng dẫn viên</h2>
        <a href="index.php?act=admin-staff" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- ✅ Hiển thị lỗi -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="index.php?act=admin-staff-store" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">

        <!-- ============ THÔNG TIN CƠ BẢN ============ -->
        <h5 class="border-bottom pb-2 mb-3">📋 Thông tin cơ bản</h5>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Họ và tên <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" required
                       placeholder="VD: Nguyễn Văn A"
                       value="<?= htmlspecialchars($_SESSION['old_data']['full_name'] ?? '') ?>">
            </div>

            <div class="col-md-6 form-group">
                <label>Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required
                       placeholder="VD: nguyenvana@gmail.com"
                       value="<?= htmlspecialchars($_SESSION['old_data']['email'] ?? '') ?>">
                <small class="text-muted">Dùng để tạo tài khoản đăng nhập</small>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>SĐT <span class="text-danger">*</span></label>
                <input type="text" name="phone" class="form-control" required
                       placeholder="VD: 0912345678"
                       value="<?= htmlspecialchars($_SESSION['old_data']['phone'] ?? '') ?>">
            </div>

            <div class="col-md-6 form-group">
                <label>Ngày sinh</label>
                <input type="date" name="date_of_birth" class="form-control"
                       value="<?= htmlspecialchars($_SESSION['old_data']['date_of_birth'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>CMND/CCCD</label>
            <input type="text" name="id_number" class="form-control"
                   placeholder="VD: 001234567890"
                   value="<?= htmlspecialchars($_SESSION['old_data']['id_number'] ?? '') ?>">
        </div>

        <!-- ============ ẢNH ĐẠI DIỆN ============ -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">📸 Ảnh đại diện</h5>

        <div class="form-group">
            <label>Chọn ảnh</label>
            <input type="file" name="profile_image" class="form-control-file" accept="image/*">
            <small class="text-muted">Định dạng: JPG, PNG, WEBP. Tối đa 2MB.</small>
        </div>

        <!-- ============ PHÂN LOẠI & NĂNG LỰC ============ -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">🎯 Phân loại & Năng lực</h5>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Phân loại HDV <span class="text-danger">*</span></label>
                <select name="staff_type" class="form-control" required>
                    <option value="DOMESTIC">🏠 Nội địa</option>
                    <option value="INTERNATIONAL">✈️ Quốc tế</option>
                    <option value="SPECIALIZED">🎯 Chuyên tuyến</option>
                    <option value="GROUP_TOUR">👥 Chuyên khách đoàn</option>
                </select>
            </div>

            <div class="col-md-6 form-group">
                <label>Trình độ/Bằng cấp</label>
                <input type="text" name="qualification" class="form-control"
                       placeholder="VD: Cử nhân Du lịch"
                       value="<?= htmlspecialchars($_SESSION['old_data']['qualification'] ?? '') ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Số năm kinh nghiệm</label>
                <input type="number" name="experience_years" class="form-control" min="0"
                       placeholder="VD: 5"
                       value="<?= htmlspecialchars($_SESSION['old_data']['experience_years'] ?? '0') ?>">
            </div>

            <div class="col-md-6 form-group">
                <label>Đánh giá năng lực (0-5)</label>
                <input type="number" name="rating" class="form-control" min="0" max="5" step="0.1"
                       placeholder="VD: 4.5"
                       value="<?= htmlspecialchars($_SESSION['old_data']['rating'] ?? '') ?>">
            </div>
        </div>

        <!-- ============ CHỨNG CHỈ & NGÔN NGỮ ============ -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">🎓 Chứng chỉ & Ngôn ngữ</h5>

        <div class="form-group">
            <label>Chứng chỉ chuyên môn</label>
            <textarea name="certifications" class="form-control" rows="3"
                      placeholder="VD: Hướng dẫn viên du lịch quốc gia số 12345, Chứng chỉ IELTS 7.5"><?= htmlspecialchars($_SESSION['old_data']['certifications'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>Ngôn ngữ sử dụng</label>
            <input type="text" name="languages" class="form-control"
                   placeholder="VD: Tiếng Anh, Tiếng Pháp, Tiếng Trung"
                   value="<?= htmlspecialchars($_SESSION['old_data']['languages'] ?? '') ?>">
            <small class="text-muted">Cách nhau bởi dấu phẩy</small>
        </div>

        <!-- ============ SỨC KHOẺ & TRẠNG THÁI ============ -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">💊 Sức khoẻ & Trạng thái</h5>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Tình trạng sức khoẻ</label>
                <select name="health_status" class="form-control">
                    <option value="good" selected>✅ Tốt</option>
                    <option value="fair">⚠️ Trung bình</option>
                    <option value="poor">❌ Yếu</option>
                </select>
            </div>

            <div class="col-md-6 form-group">
                <label>Trạng thái làm việc <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="ACTIVE" selected>✅ Đang làm</option>
                    <option value="INACTIVE">⏸️ Nghỉ việc</option>
                </select>
            </div>
        </div>

        <!-- ============ LỊCH SỬ TOUR & GHI CHÚ ============ -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">📝 Ghi chú & Khác</h5>

        <div class="form-group">
            <label>Lịch sử dẫn tour nổi bật</label>
            <textarea name="tour_history" class="form-control" rows="3"
                      placeholder="VD: Dẫn tour Hạ Long 50+ lần, Tour Sapa 30+ lần"><?= htmlspecialchars($_SESSION['old_data']['tour_history'] ?? '') ?></textarea>
            <small class="text-muted">Các tour đã dẫn, số lần, khách đặc biệt...</small>
        </div>

        <div class="form-group">
            <label>Ghi chú khác</label>
            <textarea name="notes" class="form-control" rows="3"
                      placeholder="VD: Có xe máy cá nhân, sẵn sàng tăng ca..."><?= htmlspecialchars($_SESSION['old_data']['notes'] ?? '') ?></textarea>
        </div>

        <!-- ============ BUTTONS ============ -->
        <div class="mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save"></i> Thêm mới
            </button>
            <a href="index.php?act=admin-staff" class="btn btn-secondary btn-lg">
                <i class="bi bi-x-circle"></i> Hủy
            </a>
        </div>

    </form>
</div>

<?php
// ✅ Clear old_data sau khi hiển thị
unset($_SESSION['old_data']);
?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">