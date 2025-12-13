<?php
require_once "./models/admin/PaymentModel.php";
$paymentModel = new PaymentModel();
$payments = $paymentModel->getByBooking($booking['id']);
$totalPaid = $paymentModel->getTotalPaid($booking['id']);
$remaining = (float) $booking['total_amount'] - $totalPaid;
?>

<div class="container mt-4">
    
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                📄 Chi tiết Booking #<?= htmlspecialchars($booking['booking_code']) ?>
            </h2>
            <p class="text-muted mb-0">
                <small>Được tạo lúc: <?= date('d/m/Y H:i', strtotime($booking['created_at'])) ?></small>
            </p>
        </div>
        <a href="index.php?act=admin-booking" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <!-- ALERTS -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        
        <!-- CỘT TRÁI: THÔNG TIN CHÍNH -->
        <div class="col-md-8">
            
            <!-- THÔNG TIN TOUR -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-globe"></i> Thông tin Tour</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1"><small>Tour</small></p>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($booking['tour_name']) ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1"><small>Ngày khởi hành</small></p>
                            <p class="fw-bold mb-0">
                                <i class="bi bi-calendar-event text-primary"></i>
                                <?= date('d/m/Y', strtotime($booking['depart_date'])) ?>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1"><small>Số lượng</small></p>
                            <p class="mb-0">
                                <span class="badge bg-info">
                                    👤 <?= $booking['adults'] ?> người lớn
                                </span>
                                <?php if ($booking['children'] > 0): ?>
                                    <span class="badge bg-warning">
                                        🧒 <?= $booking['children'] ?> trẻ em
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1"><small>Tổng người</small></p>
                            <p class="fw-bold mb-0 text-primary"><?= $booking['total_people'] ?> người</p>
                        </div>
                    </div>

                    <?php if (!empty($booking['special_request'])): ?>
                        <div class="alert alert-info mb-0 mt-2">
                            <strong><i class="bi bi-info-circle"></i> Yêu cầu đặc biệt:</strong><br>
                            <?= nl2br(htmlspecialchars($booking['special_request'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- THÔNG TIN KHÁCH HÀNG -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Thông tin Khách hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <p class="text-muted mb-1"><small>Họ tên</small></p>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($booking['contact_name']) ?></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <p class="text-muted mb-1"><small>Điện thoại</small></p>
                            <p class="mb-0">
                                <i class="bi bi-telephone-fill text-success"></i>
                                <?= htmlspecialchars($booking['contact_phone']) ?>
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <p class="text-muted mb-1"><small>Email</small></p>
                            <p class="mb-0">
                                <i class="bi bi-envelope-fill text-primary"></i>
                                <?= htmlspecialchars($booking['contact_email'] ?? 'Chưa có') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LỊCH SỬ THANH TOÁN -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-warning d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-credit-card-2-front"></i> Lịch sử Thanh toán</h5>
                    <a href="index.php?act=admin-payment-create&booking_id=<?= $booking['id'] ?>" 
                       class="btn btn-sm btn-success">
                        <i class="bi bi-plus-circle"></i> Thêm Payment
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> 
                            Chưa có thanh toán nào. Nhấn "Thêm Payment" để ghi nhận thanh toán.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã Payment</th>
                                        <th>Ngày</th>
                                        <th>Loại</th>
                                        <th class="text-end">Số tiền</th>
                                        <th>Phương thức</th>
                                        <th>Trạng thái</th>
                                        <th class="text-center">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $p): ?>
                                        <tr>
                                            <td>
                                                <code><?= htmlspecialchars($p['payment_code']) ?></code>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y H:i', strtotime($p['paid_at'] ?? $p['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $typeBadge = match($p['type']) {
                                                    'FULL' => '<span class="badge bg-success">Thanh toán đủ</span>',
                                                    'DEPOSIT' => '<span class="badge bg-info">Đặt cọc</span>',
                                                    'REMAINING' => '<span class="badge bg-primary">Còn lại</span>',
                                                    default => '<span class="badge bg-secondary">' . $p['type'] . '</span>'
                                                };
                                                echo $typeBadge;
                                                ?>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">
                                                    <?= number_format($p['amount']) ?>đ
                                                </strong>
                                            </td>
                                            <td>
                                                <small><?= PaymentModel::$methodLabels[$p['method']] ?? $p['method'] ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $statusBadge = match($p['status']) {
                                                    'SUCCESS' => '<span class="badge bg-success">✓ Thành công</span>',
                                                    'PENDING' => '<span class="badge bg-warning">⏳ Chờ xử lý</span>',
                                                    'FAILED' => '<span class="badge bg-danger">✗ Thất bại</span>',
                                                    'REFUNDED' => '<span class="badge bg-secondary">↩ Hoàn tiền</span>',
                                                    default => '<span class="badge bg-secondary">' . $p['status'] . '</span>'
                                                };
                                                echo $statusBadge;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="index.php?act=admin-payment-edit&id=<?= $p['id'] ?>" 
                                                       class="btn btn-warning" title="Sửa">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="index.php?act=admin-payment-delete&id=<?= $p['id'] ?>" 
                                                       class="btn btn-danger"
                                                       onclick="return confirm('Xóa payment này?')" 
                                                       title="Xóa">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Tổng đã thanh toán:</strong></td>
                                        <td class="text-end"><strong class="text-success"><?= number_format($totalPaid) ?>đ</strong></td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- LỊCH SỬ THAY ĐỔI -->
            <?php if (!empty($statusHistory)): ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Lịch sử thay đổi</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php foreach ($statusHistory as $log): ?>
                                <div class="d-flex mb-3">
                                    <div class="text-muted me-3" style="min-width: 120px;">
                                        <small><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></small>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($log['author_name'] ?? 'System') ?>:</strong>
                                        <?= htmlspecialchars($log['content']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- CỘT PHẢI: TỔNG KẾT & HÀNH ĐỘNG -->
        <div class="col-md-4">
            
            <!-- TRẠNG THÁI -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-flag"></i> Trạng thái</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="text-muted mb-1"><small>Trạng thái booking</small></p>
                        <?php
                        $tourStatusBadge = match ($booking['status']) {
                            'PENDING' => '<span class="badge bg-warning fs-6">⏳ Chờ xác nhận</span>',
                            'CONFIRMED' => '<span class="badge bg-primary fs-6">✅ Đã xác nhận</span>',
                            'PAID' => '<span class="badge bg-info fs-6">💳 Đã thanh toán</span>',
                            'COMPLETED' => '<span class="badge bg-success fs-6">🎉 Hoàn tất</span>',
                            'CANCELED' => '<span class="badge bg-danger fs-6">❌ Đã hủy</span>',
                            default => '<span class="badge bg-secondary fs-6">' . $booking['status'] . '</span>'
                        };
                        echo $tourStatusBadge;
                        ?>
                    </div>
                    
                    <div>
                        <p class="text-muted mb-1"><small>Trạng thái thanh toán</small></p>
                        <?php
                        $paymentStatusBadge = match ($booking['payment_status'] ?? 'PENDING') {
                            'FULL_PAID' => '<span class="badge bg-success fs-6">💰 Đã thanh toán đủ</span>',
                            'DEPOSIT_PAID' => '<span class="badge bg-info fs-6">💵 Đã cọc</span>',
                            default => '<span class="badge bg-secondary fs-6">⏸️ Chưa thanh toán</span>'
                        };
                        echo $paymentStatusBadge;
                        ?>
                    </div>
                </div>
            </div>

            <!-- TỔNG TIỀN -->
            <div class="card shadow-sm mb-3 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Tổng tiền</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tổng booking:</span>
                        <strong><?= number_format($booking['total_amount']) ?>đ</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Đã thanh toán:</span>
                        <strong><?= number_format($totalPaid) ?>đ</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Còn lại:</strong>
                        <h4 class="mb-0 text-danger">
                            <?= number_format($remaining) ?>đ
                        </h4>
                    </div>
                </div>
            </div>

            <!-- HÀNH ĐỘNG -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Hành động</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        
                        <?php if ($booking['status'] === 'PENDING'): ?>
                            <a href="index.php?act=admin-booking-confirm&id=<?= $booking['id'] ?>" 
                               class="btn btn-success"
                               onclick="return confirm('Xác nhận booking này?')">
                                <i class="bi bi-check-circle"></i> Xác nhận Booking
                            </a>
                        <?php endif; ?>

                        <a href="index.php?act=admin-booking-edit&id=<?= $booking['id'] ?>" 
                           class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Sửa Booking
                        </a>

                        <a href="index.php?act=admin-payment-create&booking_id=<?= $booking['id'] ?>" 
                           class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Thêm Payment
                        </a>

                        <?php if ($booking['status'] !== 'CANCELED'): ?>
                            <a href="index.php?act=admin-booking-cancel&id=<?= $booking['id'] ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('⚠️ Hủy booking này?\n\nLưu ý: Không thể hoàn tác!')">
                                <i class="bi bi-x-circle"></i> Hủy Booking
                            </a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">