<?php
// views/admin/BookingCustomer/edit.php

$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);

// $customer: dữ liệu khách hàng hiện tại
?>

<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-pencil-square"></i> Chỉnh sửa khách
            </h2>
            <p class="text-muted mb-0">
                Booking: <strong><?= htmlspecialchars($booking['booking_code']) ?></strong>
                <span class="mx-2">|</span>
                Tour: <?= htmlspecialchars($booking['tour_name']) ?>
            </p>
        </div>
        <a href="index.php?act=admin-booking-customer&booking_id=<?= $booking['id'] ?>" 
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
    <form action="index.php?act=admin-booking-customer-update&id=<?= $customer['id'] ?>" 
          method="POST" 
          class="card shadow-sm">
        
        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

        <div class="card-body">
            
            <!-- Loại khách -->
            <h5 class="border-bottom pb-2 mb-3">
                <i class="bi bi-tag"></i> Phân loại
            </h5>

            <div class="mb-4">
                <label class="form-label fw-bold">
                    Loại khách <span class="text-danger">*</span>
                </label>
                <select name="customer_type" class="form-select form-select-lg" required>
                    <option value="">-- Chọn loại khách --</option>
                    <option value="ADULT" <?= (($old['customer_type'] ?? $customer['customer_type']) === 'ADULT') ? 'selected' : '' ?>>
                        👨 Người lớn (từ 12 tuổi trở lên)
                    </option>
                    <option value="CHILD" <?= (($old['customer_type'] ?? $customer['customer_type']) === 'CHILD') ? 'selected' : '' ?>>
                        👦 Trẻ em (2-11 tuổi)
                    </option>
                    <option value="INFANT" <?= (($old['customer_type'] ?? $customer['customer_type']) === 'INFANT') ? 'selected' : '' ?>>
                        👶 Em bé (dưới 2 tuổi)
                    </option>
                </select>
            </div>

            <!-- Thông tin cơ bản -->
            <h5 class="border-bottom pb-2 mb-3">
                <i class="bi bi-person"></i> Thông tin cá nhân
            </h5>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-bold">
                        Họ và tên <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="full_name" 
                           class="form-control form-control-lg" 
                           required
                           placeholder="VD: Nguyễn Văn A"
                           value="<?= htmlspecialchars($old['full_name'] ?? $customer['full_name']) ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Ngày sinh</label>
                    <input type="date" 
                           name="date_of_birth" 
                           class="form-control form-control-lg"
                           value="<?= htmlspecialchars($old['date_of_birth'] ?? $customer['date_of_birth']) ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Giới tính</label>
                    <select name="gender" class="form-select">
                        <option value="">-- Chọn --</option>
                        <option value="MALE" <?= (($old['gender'] ?? $customer['gender']) === 'MALE') ? 'selected' : '' ?>>👨 Nam</option>
                        <option value="FEMALE" <?= (($old['gender'] ?? $customer['gender']) === 'FEMALE') ? 'selected' : '' ?>>👩 Nữ</option>
                        <option value="OTHER" <?= (($old['gender'] ?? $customer['gender']) === 'OTHER') ? 'selected' : '' ?>>👤 Khác</option>
                    </select>
                </div>

                <div class="col-md-8 mb-3">
                    <label class="form-label fw-bold">CMND/CCCD/Passport</label>
                    <input type="text" 
                           name="id_number" 
                           class="form-control"
                           placeholder="VD: 001234567890"
                           value="<?= htmlspecialchars($old['id_number'] ?? $customer['id_number']) ?>">
                </div>
            </div>

            <!-- Liên hệ -->
            <h5 class="border-bottom pb-2 mb-3 mt-4">
                <i class="bi bi-telephone"></i> Thông tin liên hệ
            </h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Số điện thoại</label>
                    <input type="text" 
                           name="phone" 
                           class="form-control"
                           placeholder="VD: 0912345678"
                           value="<?= htmlspecialchars($old['phone'] ?? $customer['phone']) ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" 
                           name="email" 
                           class="form-control"
                           placeholder="VD: example@gmail.com"
                           value="<?= htmlspecialchars($old['email'] ?? $customer['email']) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Quốc tịch</label>
                <input type="text" 
                       name="nationality" 
                       class="form-control"
                       placeholder="VD: Việt Nam"
                       value="<?= htmlspecialchars($old['nationality'] ?? $customer['nationality']) ?>">
            </div>

            <!-- Ghi chú -->
            <h5 class="border-bottom pb-2 mb-3 mt-4">
                <i class="bi bi-chat-text"></i> Ghi chú đặc biệt
            </h5>

            <div class="mb-3">
                <label class="form-label fw-bold">Ghi chú</label>
                <textarea name="notes" 
                          class="form-control" 
                          rows="3"
                          placeholder="VD: Dị ứng hải sản, ăn chay, cần hỗ trợ di chuyển..."><?= htmlspecialchars($old['notes'] ?? $customer['notes']) ?></textarea>
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> Thông tin về sức khỏe, chế độ ăn uống, yêu cầu đặc biệt...
                </small>
            </div>

        </div>

        <!-- Footer -->
        <div class="card-footer bg-light">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Cập nhật khách
                </button>
                <a href="index.php?act=admin-booking-customer&booking_id=<?= $booking['id'] ?>" 
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
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
