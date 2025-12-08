<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">💳 Thêm Payment</h2>
            <p class="text-muted mb-0">
                <small>
                    Booking: <strong>#<?= htmlspecialchars($booking['booking_code']) ?></strong> - 
                    <?= htmlspecialchars($booking['contact_name']) ?>
                </small>
            </p>
        </div>
        <a href="index.php?act=admin-booking-detail&id=<?= $booking['id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

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
            <form action="index.php?act=admin-payment-store" method="POST">
                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

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
                                   value="<?= $remaining > 0 ? $remaining : $booking['total_amount'] ?>"
                                   required>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Còn lại: <strong class="text-danger"><?= number_format($remaining) ?>đ</strong>
                            </small>
                        </div>

                        <!-- Loại thanh toán -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Loại thanh toán <span class="text-danger">*</span>
                                </label>
                                <select name="type" class="form-select" required>
                                    <option value="FULL">Thanh toán đủ</option>
                                    <option value="DEPOSIT" <?= $totalPaid == 0 ? 'selected' : '' ?>>Đặt cọc</option>
                                    <option value="REMAINING" <?= $totalPaid > 0 ? 'selected' : '' ?>>Thanh toán còn lại</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Phương thức <span class="text-danger">*</span>
                                </label>
                                <select name="method" class="form-select" required>
                                    <option value="CASH">💵 Tiền mặt</option>
                                    <option value="BANK_TRANSFER" selected>🏦 Chuyển khoản</option>
                                    <option value="CREDIT_CARD">💳 Thẻ tín dụng</option>
                                    <option value="MOMO">📱 MoMo</option>
                                    <option value="VNPAY">🔵 VNPay</option>
                                    <option value="ZALOPAY">🔴 ZaloPay</option>
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
                                    <option value="SUCCESS" selected>✅ Thành công</option>
                                    <option value="PENDING">⏳ Chờ xử lý</option>
                                    <option value="FAILED">❌ Thất bại</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Ngày thanh toán <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="paid_at" class="form-control" 
                                       value="<?= date('Y-m-d\TH:i') ?>" required>
                            </div>
                        </div>

                        <!-- Ghi chú -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi chú</label>
                            <textarea name="note" class="form-control" rows="3" 
                                      placeholder="VD: Chuyển khoản qua STK 1234567890 - ACB"></textarea>
                        </div>

                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle"></i> Lưu Payment
                    </button>
                    <a href="index.php?act=admin-booking-detail&id=<?= $booking['id'] ?>" 
                       class="btn btn-secondary btn-lg">
                        <i class="bi bi-x-circle"></i> Hủy
                    </a>
                </div>
            </form>
        </div>

        <!-- CỘT PHẢI: TỔNG KẾT -->
        <div class="col-md-4">
            
            <!-- Thông tin booking -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-receipt"></i> Thông tin Booking</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tổng booking:</span>
                        <strong><?= number_format($booking['total_amount']) ?>đ</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Đã thanh toán:</span>
                        <strong class="text-success"><?= number_format($totalPaid) ?>đ</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Còn lại:</strong>
                        <h5 class="mb-0 text-danger">
                            <?= number_format($remaining) ?>đ
                        </h5>
                    </div>
                </div>
            </div>

            <!-- Payments đã có -->
            <?php if (!empty($existingPayments)): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="bi bi-clock-history"></i> Thanh toán trước đó</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($existingPayments as $p): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div>
                                    <small class="text-muted d-block">
                                        <?= date('d/m/Y', strtotime($p['paid_at'] ?? $p['created_at'])) ?>
                                    </small>
                                    <span class="badge bg-<?= $p['status'] == 'SUCCESS' ? 'success' : 'warning' ?>">
                                        <?= PaymentModel::$typeLabels[$p['type']] ?? $p['type'] ?>
                                    </span>
                                </div>
                                <strong class="text-success">
                                    <?= number_format($p['amount']) ?>đ
                                </strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">