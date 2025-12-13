<?php
// ✅ Lấy dữ liệu cũ nếu có lỗi
$oldData = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);

// ✅ Merge với dữ liệu hiện tại
if (!empty($oldData)) {
    $staff = array_merge($staff, $oldData);
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">✏️ Sửa Nhân viên</h2>
        <a href="index.php?act=admin-staff" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <form action="index.php?act=admin-staff-update" method="POST" enctype="multipart/form-data"
        class="card p-4 shadow-sm">
        <input type="hidden" name="id" value="<?= $staff['id'] ?>">

        <!-- ✅ THÊM THÔNG BÁO LỖI -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- ============ THÔNG TIN CƠ BẢN ============ -->
        <h5 class="border-bottom pb-2 mb-3">📋 Thông tin cơ bản</h5>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Họ và tên <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" required
                       placeholder="VD: Nguyễn Văn A"
                       value="<?= htmlspecialchars($staff['full_name']) ?>">
            </div>

            <div class="col-md-6 form-group">
                <label>Ngày sinh</label>
                <input type="date" name="date_of_birth" class="form-control"
                    value="<?= htmlspecialchars($staff['date_of_birth'] ?? '') ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>SĐT <span class="text-danger">*</span></label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($staff['phone']) ?>"
                    required>
            </div>

            <div class="col-md-6 form-group">
                <label>CMND/CCCD</label>
                <input type="text" name="id_number" class="form-control"
                    value="<?= htmlspecialchars($staff['id_number'] ?? '') ?>">
            </div>
        </div>

        <!-- ============ ẢNH ĐẠI DIỆN ============ -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">📸 Ảnh đại diện</h5>

        <div class="form-group">
            <label>Ảnh hiện tại</label><br>
            <?php if (!empty($staff['profile_image'])): ?>
                <img src="<?= htmlspecialchars($staff['profile_image']) ?>" alt="Avatar" class="rounded mb-2"
                    style="width: 120px; height: 120px; object-fit: cover;">
            <?php else: ?>
                <p class="text-muted">Chưa có ảnh</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Chọn ảnh mới (nếu muốn thay đổi)</label>
            <input type="file" name="profile_image" class="form-control-file" accept="image/*">
            <small class="text-muted">Định dạng: JPG, PNG, WEBP. Tối đa 2MB.</small>
        </div>
        <input type="hidden" name="old_profile_image" value="<?= htmlspecialchars($staff['profile_image'] ?? '') ?>">

        <!-- ============ PHÂN LOẠI & NĂNG LỰC ============ -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">🎯 Phân loại & Năng lực</h5>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Phân loại HDV <span class="text-danger">*</span></label>
                <select name="staff_type" class="form-control" required>
                    <option value="DOMESTIC" <?= ($staff['staff_type'] ?? '') == 'DOMESTIC' ? 'selected' : '' ?>>
                        🏠 Nội địa
                    </option>
                    <option value="INTERNATIONAL" <?= ($staff['staff_type'] ?? '') == 'INTERNATIONAL' ? 'selected' : '' ?>>
                        ✈️ Quốc tế
                    </option>
                    <option value="SPECIALIZED" <?= ($staff['staff_type'] ?? '') == 'SPECIALIZED' ? 'selected' : '' ?>>
                        🎯 Chuyên tuyến
                    </option>
                    <option value="GROUP_TOUR" <?= ($staff['staff_type'] ?? '') == 'GROUP_TOUR' ? 'selected' : '' ?>>
                        👥 Chuyên khách đoàn
                    </option>
                </select>
            </div>

            <div class="col-md-6 form-group">
                <label>Trình độ/Bằng cấp</label>
                <input type="text" name="qualification" class="form-control"
                    value="<?= htmlspecialchars($staff['qualification'] ?? '') ?>" placeholder="VD: Cử nhân Du lịch">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Số năm kinh nghiệm</label>
                <input type="number" name="experience_years" class="form-control"
                    value="<?= htmlspecialchars($staff['experience_years'] ?? 0) ?>" min="0" placeholder="VD: 5">
            </div>

            <div class="col-md-6 form-group">
                <label>Đánh giá năng lực (0-5)</label>
                <input type="number" name="rating" class="form-control"
                    value="<?= htmlspecialchars($staff['rating'] ?? '') ?>" min="0" max="5" step="0.1"
                    placeholder="VD: 4.5">
            </div>
        </div>

        <!-- ============ CHỨNG CHỈ & NGÔN NGỮ ============ -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">🎓 Chứng chỉ & Ngôn ngữ</h5>

        <div class="form-group">
            <label>Chứng chỉ chuyên môn</label>
            <textarea name="certifications" class="form-control" rows="3"
                placeholder="VD: Hướng dẫn viên du lịch quốc gia số 12345, Chứng chỉ IELTS 7.5"><?= htmlspecialchars($staff['certifications'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>Ngôn ngữ sử dụng</label>
            <input type="text" name="languages" class="form-control"
                value="<?= htmlspecialchars($staff['languages'] ?? '') ?>"
                placeholder="VD: Tiếng Anh, Tiếng Pháp, Tiếng Trung">
            <small class="text-muted">Cách nhau bởi dấu phẩy</small>
        </div>

        <!-- ============ SỨC KHOẺ & TRẠNG THÁI ============ -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">💊 Sức khoẻ & Trạng thái</h5>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Tình trạng sức khoẻ</label>
                <select name="health_status" class="form-control">
                    <option value="good" <?= ($staff['health_status'] ?? 'good') == 'good' ? 'selected' : '' ?>>
                        ✅ Tốt
                    </option>
                    <option value="fair" <?= ($staff['health_status'] ?? '') == 'fair' ? 'selected' : '' ?>>
                        ⚠️ Trung bình
                    </option>
                    <option value="poor" <?= ($staff['health_status'] ?? '') == 'poor' ? 'selected' : '' ?>>
                        ❌ Yếu
                    </option>
                </select>
            </div>

            <div class="col-md-6 form-group">
                <label>Trạng thái làm việc <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="ACTIVE" <?= ($staff['status'] ?? 'ACTIVE') == 'ACTIVE' ? 'selected' : '' ?>>
                        ✅ Đang làm
                    </option>
                    <option value="INACTIVE" <?= ($staff['status'] ?? '') == 'INACTIVE' ? 'selected' : '' ?>>
                        ⏸️ Nghỉ việc
                    </option>
                </select>
            </div>
        </div>

        <!-- ============ LỊCH SỬ TOUR & GHI CHÚ ============ -->
        <h5 class="border-bottom pb-2 mb-3 mt-4">📝 Ghi chú & Khác</h5>

        <div class="form-group">
            <label>Lịch sử dẫn tour nổi bật</label>
            <textarea name="tour_history" class="form-control" rows="3"
                placeholder="VD: Dẫn tour Hạ Long 50+ lần, Tour Sapa 30+ lần"><?= htmlspecialchars($staff['tour_history'] ?? '') ?></textarea>
            <small class="text-muted">Các tour đã dẫn, số lần, khách đặc biệt...</small>
        </div>

        <div class="form-group">
            <label>Ghi chú khác</label>
            <textarea name="notes" class="form-control" rows="3"
                placeholder="VD: Có xe máy cá nhân, sẵn sàng tăng ca..."><?= htmlspecialchars($staff['notes'] ?? '') ?></textarea>
        </div>

        <!-- ============ BUTTONS ============ -->
        <div class="mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save"></i> Cập nhật
            </button>
            <a href="index.php?act=admin-staff" class="btn btn-secondary btn-lg">
                <i class="bi bi-x-circle"></i> Hủy
            </a>
        </div>

    </form>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">