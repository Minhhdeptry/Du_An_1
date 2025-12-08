<?php
// ============================================
// FILE 1: views/admin/Staff/certificate_create.php
// ============================================
$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>➕ Thêm chứng chỉ - <?= htmlspecialchars($staff['full_name']) ?></h2>
        <a href="index.php?act=admin-staff-cert&staff_id=<?= $staff['id'] ?>" class="btn btn-secondary">
            ← Quay lại
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="index.php?act=admin-staff-cert-store" method="POST" enctype="multipart/form-data" class="card p-4">
        <input type="hidden" name="staff_id" value="<?= $staff['id'] ?>">

        <div class="form-group mb-3">
            <label class="fw-bold">Tên chứng chỉ <span class="text-danger">*</span></label>
            <input type="text" name="certificate_name" class="form-control" required
                   placeholder="VD: Hướng dẫn viên du lịch quốc gia"
                   value="<?= htmlspecialchars($old['certificate_name'] ?? '') ?>">
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="fw-bold">Số chứng chỉ</label>
                <input type="text" name="certificate_number" class="form-control"
                       placeholder="VD: 123456/TCDL"
                       value="<?= htmlspecialchars($old['certificate_number'] ?? '') ?>">
            </div>

            <div class="col-md-6 form-group mb-3">
                <label class="fw-bold">Đơn vị cấp</label>
                <input type="text" name="issuing_organization" class="form-control"
                       placeholder="VD: Tổng cục Du lịch"
                       value="<?= htmlspecialchars($old['issuing_organization'] ?? '') ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="fw-bold">Ngày cấp</label>
                <input type="date" name="issue_date" class="form-control"
                       value="<?= htmlspecialchars($old['issue_date'] ?? '') ?>">
            </div>

            <div class="col-md-6 form-group mb-3">
                <label class="fw-bold">Ngày hết hạn</label>
                <input type="date" name="expiry_date" class="form-control"
                       value="<?= htmlspecialchars($old['expiry_date'] ?? '') ?>">
                <small class="text-muted">Để trống nếu không có hạn sử dụng</small>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold">File chứng chỉ (PDF, JPG, PNG)</label>
            <input type="file" name="certificate_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            <small class="text-muted">Tối đa 5MB</small>
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold">Ghi chú</label>
            <textarea name="notes" class="form-control" rows="3"
                      placeholder="Ghi chú thêm về chứng chỉ này..."><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">💾 Lưu chứng chỉ</button>
            <a href="index.php?act=admin-staff-cert&staff_id=<?= $staff['id'] ?>" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
