<?php
// views/admin/Payment/edit.php

// Lấy dữ liệu cũ nếu có lỗi
$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);

// Merge với dữ liệu hiện tại
if (!empty($old)) {
    $payment = array_merge($payment, $old);
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">✏️ Sửa Payment</h2>
            <p class="text-muted mb-0">
                <small>
                    Mã payment: <strong><?= htmlspecialchars($payment['payment_code'] ?? 'N/A') ?></strong>
                </small>
            </p>
        </div>
        <a href="index.php?act=admin-payment-history&booking_id=<?= $payment['booking_id'] ?>" 
           class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Thông báo lỗi -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <form action="index.php?act=admin-payment-update" method="POST">
                <input type="hidden" name="id" value="<?= $payment['id'] ?>">
                <input type="hidden" name="booking_id" value="<?= $payment['booking_id'] ?>">

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Thông tin Thanh toán</h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- Số tiền -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Số tiền <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="amount" class="form-control form-control-lg" 
                                   min="0" step="1000" 
                                   value="<?= htmlspecialchars($payment['amount']) ?>"
                                   required>
                        </div>

                        <!-- Loại thanh toán & Phương thức -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Loại thanh toán <span class="text-danger">*</span>
                                </label>
                                <select name="type" class="form-select" required>
                                    <option value="FULL" <?= ($payment['type'] ?? '') == 'FULL' ? 'selected' : '' ?>>
                                        Thanh toán đủ
                                    </option>
                                    <option value="DEPOSIT" <?= ($payment['type'] ?? '') == 'DEPOSIT' ? 'selected' : '' ?>>
                                        Đặt cọc
                                    </option>
                                    <option value="REMAINING" <?= ($payment['type'] ?? '') == 'REMAINING' ? 'selected' : '' ?>>
                                        Thanh toán còn lại
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Phương thức <span class="text-danger">*</span>
                                </label>
                                <select name="method" class="form-select" required>
                                    <option value="CASH" <?= ($payment['method'] ?? '') == 'CASH' ? 'selected' : '' ?>>
                                        💵 Tiền mặt
                                    </option>
                                    <option value="BANK_TRANSFER" <?= ($payment['method'] ?? '') == 'BANK_TRANSFER' ? 'selected' : '' ?>>
                                        🏦 Chuyển khoản
                                    </option>
                                    <option value="CREDIT_CARD" <?= ($payment['method'] ?? '') == 'CREDIT_CARD' ? 'selected' : '' ?>>
                                        💳 Thẻ tín dụng
                                    </option>
                                    <option value="MOMO" <?= ($payment['method'] ?? '') == 'MOMO' ? 'selected' : '' ?>>
                                        📱 MoMo
                                    </option>
                                    <option value="VNPAY" <?= ($payment['method'] ?? '') == 'VNPAY' ? 'selected' : '' ?>>
                                        🔵 VNPay
                                    </option>
                                    <option value="ZALOPAY" <?= ($payment['method'] ?? '') == 'ZALOPAY' ? 'selected' : '' ?>>
                                        🔴 ZaloPay
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Trạng thái & Ngày thanh toán -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Trạng thái <span class="text-danger">*</span>
                                </label>
                                <select name="status" class="form-select" required>
                                    <option value="SUCCESS" <?= ($payment['status'] ?? '') == 'SUCCESS' ? 'selected' : '' ?>>
                                        ✅ Thành công
                                    </option>
                                    <option value="PENDING" <?= ($payment['status'] ?? '') == 'PENDING' ? 'selected' : '' ?>>
                                        ⏳ Chờ xử lý
                                    </option>
                                    <option value="FAILED" <?= ($payment['status'] ?? '') == 'FAILED' ? 'selected' : '' ?>>
                                        ❌ Thất bại
                                    </option>
                                    <option value="REFUNDED" <?= ($payment['status'] ?? '') == 'REFUNDED' ? 'selected' : '' ?>>
                                        ↩️ Hoàn tiền
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Ngày thanh toán <span class="text-danger">*</span>
                                </label>
                                <?php
                                $paidAt = !empty($payment['paid_at']) 
                                    ? date('Y-m-d\TH:i', strtotime($payment['paid_at'])) 
                                    : date('Y-m-d\TH:i');
                                ?>
                                <input type="datetime-local" name="paid_at" class="form-control" 
                                       value="<?= htmlspecialchars($paidAt) ?>" required>
                            </div>
                        </div>

                        <!-- Ghi chú -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi chú</label>
                            <textarea name="note" class="form-control" rows="3" 
                                      placeholder="VD: Chuyển khoản qua STK 1234567890 - ACB"><?= htmlspecialchars($payment['note'] ?? '') ?></textarea>
                        </div>

                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle"></i> Cập nhật Payment
                    </button>
                    <a href="index.php?act=admin-payment-history&booking_id=<?= $payment['booking_id'] ?>" 
                       class="btn btn-secondary btn-lg">
                        <i class="bi bi-x-circle"></i> Hủy
                    </a>
                </div>
            </form>
        </div>

        <!-- CỘT PHẢI: THÔNG TIN BOOKING -->
        <div class="col-md-4">
            
            <!-- Thông tin booking -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-receipt"></i> Thông tin Booking</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <small class="text-muted">Mã booking:</small><br>
                        <strong><?= htmlspecialchars($booking['booking_code'] ?? 'N/A') ?></strong>
                    </p>
                    <p class="mb-2">
                        <small class="text-muted">Khách hàng:</small><br>
                        <strong><?= htmlspecialchars($booking['contact_name'] ?? 'N/A') ?></strong>
                    </p>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tổng booking:</span>
                        <strong><?= number_format($booking['total_amount'] ?? 0) ?>đ</strong>
                    </div>
                </div>
            </div>

            <!-- Thông tin payment hiện tại -->
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Lưu ý</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">
                            <small>Thay đổi status sẽ ảnh hưởng đến trạng thái booking</small>
                        </li>
                        <li class="mb-2">
                            <small>Status <strong>SUCCESS</strong> sẽ tự động cập nhật trạng thái booking</small>
                        </li>
                        <li>
                            <small>Nếu thanh toán đủ, booking sẽ chuyển sang <strong>COMPLETED</strong></small>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<script>
// Auto dismiss alerts sau 5s
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