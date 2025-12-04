<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075);
    }

    .table-hover thead tr:hover {
        background-color: #343a40 !important;
        /* giữ màu dark cho thead */
    }
</style>
<h2 class="mb-4">💳 Lịch sử thanh toán — Booking #<?= $booking_id ?></h2>

<!-- Nút quay lại -->
<a href="index.php?act=admin-payment" class="btn btn-secondary mb-3">
    ← Quay lại danh sách
</a>

<table class="table table-hover table-bordered align-middle">
    <thead class="table-dark text-center">
        <tr>
            <th>ID</th>
            <th>Số tiền</th>
            <th>Loại</th>
            <th>Phương thức</th>
            <th>Trạng thái</th>
            <th>Mã giao dịch</th>
            <th>Ngày thanh toán</th>
        </tr>
    </thead>

    <tbody class="text-center">
        <?php if (empty($payments)): ?>
            <tr>
                <td colspan="7" class="text-muted">Không có lịch sử thanh toán nào.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= number_format($p['amount']) ?>đ</td>
                    <td><span class="badge bg-info text-dark"><?= $p['type'] ?? '-' ?></span></td>
                    <td><?= $p['method'] ?></td>
                    <td>
                        <?php if ($p['status'] === 'PENDING'): ?>
                            <span class="badge bg-warning text-dark">⏳ Chờ xác nhận</span>
                        <?php elseif ($p['status'] === 'SUCCESS'): ?>
                            <span class="badge bg-success">💵 Thành công</span>
                        <?php elseif ($p['status'] === 'FAILED'): ?>
                            <span class="badge bg-danger">❌ Thất bại</span>
                        <?php elseif ($p['status'] === 'REFUNDED'): ?>
                            <span class="badge bg-secondary">♻️ Đã hoàn tiền</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $p['transaction_code'] ?: '-' ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['paid_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>