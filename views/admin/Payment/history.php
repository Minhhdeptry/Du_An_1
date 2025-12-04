<h2 class="mb-4">Lịch sử thanh toán — Booking #<?= $booking_id ?></h2>

<!-- Nút quay lại -->
<a href="index.php?act=admin-payment" class="btn btn-secondary mb-3">
    ← Quay lại danh sách
</a>

<table class="table table-striped table-bordered">
    <thead class="thead-dark">
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

    <tbody>
        <?php if (empty($payments)): ?>
            <tr>
                <td colspan="7" class="text-center text-muted">
                    Không có lịch sử thanh toán nào.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= number_format($p['amount']) ?>đ</td>
                    <td><span class="badge badge-info"><?= $p['type'] ?></span></td>
                    <td><?= $p['method'] ?></td>

                    <td>
                        <?php if ($p['status'] === 'PENDING'): ?>
                            <span class="badge badge-warning">Chờ xác nhận</span>

                        <?php elseif ($p['status'] === 'SUCCESS'): ?>
                            <span class="badge badge-success">Thành công</span>

                        <?php elseif ($p['status'] === 'FAILED'): ?>
                            <span class="badge badge-danger">Thất bại</span>

                        <?php elseif ($p['status'] === 'REFUNDED'): ?>
                            <span class="badge badge-secondary">Đã hoàn tiền</span>

                        <?php endif; ?>
                    </td>


                    <td><?= $p['transaction_code'] ?: '-' ?></td>
                    <td><?= $p['paid_at'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>