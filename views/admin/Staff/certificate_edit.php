<?php
// ============================================
// FILE 2: views/admin/Staff/certificate_edit.php
// ============================================
?>

<div class="container mt-4">
    <h2>✏️ Sửa chứng chỉ - <?= htmlspecialchars($staff['full_name']) ?></h2>

    <form action="index.php?act=admin-staff-cert-update" method="POST" enctype="multipart/form-data" class="card p-4 mt-3">
        <input type="hidden" name="id" value="<?= $certificate['id'] ?>">

        <div class="form-group mb-3">
            <label class="fw-bold">Tên chứng chỉ <span class="text-danger">*</span></label>
            <input type="text" name="certificate_name" class="form-control" required
                value="<?= htmlspecialchars($certificate['certificate_name']) ?>">
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="fw-bold">Số chứng chỉ</label>
                <input type="text" name="certificate_number" class="form-control"
                    value="<?= htmlspecialchars($certificate['certificate_number'] ?? '') ?>">
            </div>

            <div class="col-md-6 form-group mb-3">
                <label class="fw-bold">Đơn vị cấp</label>
                <input type="text" name="issuing_organization" class="form-control"
                    value="<?= htmlspecialchars($certificate['issuing_organization'] ?? '') ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label class="fw-bold">Ngày cấp</label>
                <input type="date" name="issue_date" class="form-control"
                    value="<?= htmlspecialchars($certificate['issue_date'] ?? '') ?>">
            </div>

            <div class="col-md-6 form-group mb-3">
                <label class="fw-bold">Ngày hết hạn</label>
                <input type="date" name="expiry_date" class="form-control"
                    value="<?= htmlspecialchars($certificate['expiry_date'] ?? '') ?>">
            </div>
        </div>

        <?php if (!empty($certificate['certificate_file'])): ?>
            <div class="alert alert-info">
                📄 File hiện tại: <a href="<?= htmlspecialchars($certificate['certificate_file']) ?>" target="_blank">Xem file</a>
            </div>
        <?php endif; ?>

        <div class="form-group mb-3">
            <label class="fw-bold">Thay đổi file (nếu cần)</label>
            <input type="file" name="certificate_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold">Ghi chú</label>
            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($certificate['notes'] ?? '') ?></textarea>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">💾 Cập nhật</button>
            <a href="index.php?act=admin-staff-cert&staff_id=<?= $certificate['staff_id'] ?>" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">