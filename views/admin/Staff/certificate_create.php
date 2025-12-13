<?php
// views/admin/Staff/certificate_create.php

$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);
?>

<div class="container mt-4">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-award"></i> Thêm chứng chỉ
            </h2>
            <p class="text-muted mb-0">
                Hướng dẫn viên: <strong><?= htmlspecialchars($staff['full_name']) ?></strong>
            </p>
        </div>
        <a href="index.php?act=admin-staff-cert&staff_id=<?= $staff['id'] ?>" 
           class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Error Alert -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Form -->
    <form action="index.php?act=admin-staff-cert-store" 
          method="POST" 
          enctype="multipart/form-data" 
          class="card shadow-sm">
        
        <input type="hidden" name="staff_id" value="<?= $staff['id'] ?>">

        <div class="card-body">
            
            <!-- Thông tin cơ bản -->
            <h5 class="border-bottom pb-2 mb-3">
                <i class="bi bi-info-circle"></i> Thông tin chứng chỉ
            </h5>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-bold">
                        Tên chứng chỉ <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="certificate_name" 
                           class="form-control form-control-lg" 
                           required
                           placeholder="VD: Hướng dẫn viên du lịch quốc gia"
                           value="<?= htmlspecialchars($old['certificate_name'] ?? '') ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Số chứng chỉ</label>
                    <input type="text" 
                           name="certificate_number" 
                           class="form-control form-control-lg"
                           placeholder="VD: 123456/TCDL"
                           value="<?= htmlspecialchars($old['certificate_number'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Đơn vị cấp</label>
                <input type="text" 
                       name="issuing_organization" 
                       class="form-control"
                       placeholder="VD: Tổng cục Du lịch Việt Nam"
                       value="<?= htmlspecialchars($old['issuing_organization'] ?? '') ?>">
            </div>

            <!-- Ngày tháng -->
            <h5 class="border-bottom pb-2 mb-3 mt-4">
                <i class="bi bi-calendar"></i> Thời hạn
            </h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Ngày cấp</label>
                    <input type="date" 
                           name="issue_date" 
                           class="form-control"
                           value="<?= htmlspecialchars($old['issue_date'] ?? '') ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Ngày hết hạn</label>
                    <input type="date" 
                           name="expiry_date" 
                           class="form-control"
                           value="<?= htmlspecialchars($old['expiry_date'] ?? '') ?>">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> 
                        Để trống nếu chứng chỉ không có thời hạn
                    </small>
                </div>
            </div>

            <!-- File upload -->
            <h5 class="border-bottom pb-2 mb-3 mt-4">
                <i class="bi bi-file-earmark-pdf"></i> Tài liệu đính kèm
            </h5>

            <div class="mb-3">
                <label class="form-label fw-bold">File chứng chỉ</label>
                <input type="file" 
                       name="certificate_file" 
                       class="form-control" 
                       accept=".pdf,.jpg,.jpeg,.png">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> 
                    Định dạng: PDF, JPG, PNG. Tối đa 5MB.
                </small>
            </div>

            <!-- Ghi chú -->
            <div class="mb-3">
                <label class="form-label fw-bold">Ghi chú</label>
                <textarea name="notes" 
                          class="form-control" 
                          rows="3"
                          placeholder="Ghi chú thêm về chứng chỉ này..."><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
            </div>

        </div>

        <!-- Footer -->
        <div class="card-footer bg-light">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Lưu chứng chỉ
                </button>
                <a href="index.php?act=admin-staff-cert&staff_id=<?= $staff['id'] ?>" 
                   class="btn btn-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Hủy
                </a>
            </div>
        </div>

    </form>

</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<script>
// Auto dismiss alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Validate ngày hết hạn phải sau ngày cấp
document.querySelector('input[name="expiry_date"]').addEventListener('change', function() {
    const issueDate = document.querySelector('input[name="issue_date"]').value;
    const expiryDate = this.value;
    
    if (issueDate && expiryDate && expiryDate < issueDate) {
        alert('Ngày hết hạn phải sau ngày cấp!');
        this.value = '';
    }
});
</script>