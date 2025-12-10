<?php
// views/admin/Payment/history.php
?>

<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075);
    }

    .table-hover thead tr:hover {
        background-color: #343a40 !important;
    }
</style>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">💳 Lịch sử thanh toán</h2>
            <?php if (!empty($booking)): ?>
                <p class="text-muted mb-0">
                    <small>
                        Booking: <strong>#<?= htmlspecialchars($booking['booking_code']) ?></strong> - 
                        <?= htmlspecialchars($booking['contact_name']) ?>
                    </small>
                </p>
            <?php endif; ?>
        </div>
        <div>
            <a href="index.php?act=admin-payment-create&booking_id=<?= $booking_id ?>" 
               class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Thêm Payment
            </a>
            <a href="index.php?act=admin-booking-detail&id=<?= $booking_id ?>" 
               class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại Booking
            </a>
        </div>
    </div>

    <!-- Tổng quan thanh toán -->
    <?php if (!empty($booking)): ?>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Tổng booking</h6>
                        <h3 class="mb-0"><?= number_format($booking['total_amount']) ?>đ</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Đã thanh toán</h6>
                        <h3 class="mb-0"><?= number_format($booking['total_paid'] ?? 0) ?>đ</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center <?= ($booking['remaining'] ?? 0) > 0 ? 'bg-danger' : 'bg-secondary' ?> text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Còn lại</h6>
                        <h3 class="mb-0"><?= number_format($booking['remaining'] ?? 0) ?>đ</h3>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bảng lịch sử thanh toán -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-dark text-center">
                    <tr>
                        <th width="50">STT</th>
                        <th width="120">Mã Payment</th>
                        <th width="150">Ngày thanh toán</th>
                        <th width="100">Loại</th>
                        <th width="130" class="text-end">Số tiền</th>
                        <th width="120">Phương thức</th>
                        <th width="120">Trạng thái</th>
                        <th width="200">Ghi chú</th>
                        <th width="150">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ddd;"></i>
                                <p class="mt-2 mb-0">Không có lịch sử thanh toán nào.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $i => $p): ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                
                                <!-- ✅ Hiển thị payment_code -->
                                <td class="text-center">
                                    <code class="bg-light px-2 py-1 rounded">
                                        <?= htmlspecialchars($p['payment_code'] ?? 'N/A') ?>
                                    </code>
                                </td>
                                
                                <td class="text-center">
                                    <small><?= date('d/m/Y H:i', strtotime($p['paid_at'] ?? $p['created_at'])) ?></small>
                                </td>
                                
                                <td class="text-center">
                                    <?php
                                    $typeBadge = match($p['type']) {
                                        'FULL' => '<span class="badge bg-success">Thanh toán đủ</span>',
                                        'DEPOSIT' => '<span class="badge bg-info">Đặt cọc</span>',
                                        'REMAINING' => '<span class="badge bg-primary">Còn lại</span>',
                                        default => '<span class="badge bg-secondary">' . htmlspecialchars($p['type']) . '</span>'
                                    };
                                    echo $typeBadge;
                                    ?>
                                </td>
                                
                                <td class="text-end">
                                    <strong class="text-success">
                                        <?= number_format($p['amount']) ?>đ
                                    </strong>
                                </td>
                                
                                <td class="text-center">
                                    <small><?= PaymentModel::$methodLabels[$p['method']] ?? $p['method'] ?></small>
                                </td>
                                
                                <td class="text-center">
                                    <?php
                                    $statusBadge = match($p['status']) {
                                        'SUCCESS' => '<span class="badge bg-success">✓ Thành công</span>',
                                        'PENDING' => '<span class="badge bg-warning">⏳ Chờ xử lý</span>',
                                        'FAILED' => '<span class="badge bg-danger">✗ Thất bại</span>',
                                        'REFUNDED' => '<span class="badge bg-secondary">↩ Hoàn tiền</span>',
                                        default => '<span class="badge bg-secondary">' . htmlspecialchars($p['status']) . '</span>'
                                    };
                                    echo $statusBadge;
                                    ?>
                                </td>
                                
                                <td>
                                    <small><?= htmlspecialchars($p['note'] ?? '-') ?></small>
                                </td>
                                
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?act=admin-payment-edit&id=<?= $p['id'] ?>" 
                                           class="btn btn-warning" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <?php if ($p['status'] === 'PENDING'): ?>
                                            <a href="index.php?act=admin-payment-confirm&id=<?= $p['id'] ?>" 
                                               class="btn btn-success" title="Xác nhận"
                                               onclick="return confirm('Xác nhận thanh toán này?')">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="index.php?act=admin-payment-delete&id=<?= $p['id'] ?>" 
                                           class="btn btn-danger" title="Xóa"
                                           onclick="return confirm('⚠️ Xóa payment này?\n\nLưu ý: Không thể hoàn tác!')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                
                <?php if (!empty($payments)): ?>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end"><strong>Tổng đã thanh toán:</strong></td>
                            <td class="text-end">
                                <strong class="text-success fs-5">
                                    <?php
                                    $totalPaid = array_sum(array_column(
                                        array_filter($payments, fn($p) => $p['status'] === 'SUCCESS'),
                                        'amount'
                                    ));
                                    echo number_format($totalPaid);
                                    ?>đ
                                </strong>
                            </td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">