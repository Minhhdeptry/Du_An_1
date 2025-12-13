<style>
        .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075);
    }

    .table-hover thead tr:hover {
        background-color: #343a40 !important;
        /* giữ màu dark cho thead */
    }
</style>
<h2 class="mb-4">💵 Quản lý thanh toán</h2>

<table class="table table-hover table-bordered align-middle">
    <thead class="table-dark text-center">
        <tr>
            <th>ID</th>
            <th>Booking</th>
            <th>Khách hàng</th>
            <th>Số tiền</th>
            <th>Loại</th>
            <th>Phương thức</th>
            <th>Trạng thái</th>
            <th>Ngày</th>
            <th>Hành động</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($payments as $p): ?>
            <tr class="text-center">
                <td><?= $p['id'] ?></td>
                <td>#<?= $p['booking_id'] ?></td>
                <td><?= $p['contact_name'] ?></td>
                <td><?= number_format($p['amount']) ?>đ</td>
                <td><span class="badge bg-info text-dark"><?= $p['type'] ?? '-' ?></span></td>
                <td><?= $p['method'] ?></td>
                <td>
                    <?php if ($p['status'] == 'PENDING'): ?>
                        <span class="badge bg-warning text-dark">⏳ Chờ xác nhận</span>
                    <?php elseif ($p['status'] == 'SUCCESS'): ?>
                        <span class="badge bg-success">💵 Đã thanh toán</span>
                    <?php elseif ($p['status'] == 'FAILED'): ?>
                        <span class="badge bg-danger">❌ Thất bại</span>
                    <?php elseif ($p['status'] == 'REFUNDED'): ?>
                        <span class="badge bg-secondary">♻️ Hoàn / Hủy</span>
                    <?php endif; ?>
                </td>
                <td><?= !empty($p['paid_at']) ? date('d/m/Y H:i', strtotime($p['paid_at'])) : '-' ?></td>
                <td class="d-flex justify-content-center gap-1 flex-wrap">
                    <!-- Xem lịch sử -->
                    <a href="index.php?act=admin-payment-history&booking_id=<?= $p['booking_id'] ?>"
                       class="btn btn-info btn-sm">
                        Lịch sử
                    </a>

                    <?php if ($p['status'] === 'PENDING'): ?>
                        <a href="index.php?act=admin-payment-confirm&id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">
                            Xác nhận
                        </a>
                    <?php elseif ($p['status'] === 'SUCCESS'): ?>
                        <a href="index.php?act=admin-payment-cancel&id=<?= $p['id'] ?>" 
                           class="btn btn-warning btn-sm"
                           onclick="return confirm('Bạn có chắc muốn hủy xác nhận thanh toán này?')">
                            Hủy xác nhận
                        </a>
                    <?php elseif ($p['status'] === 'REFUNDED'): ?>
                        <a href="index.php?act=admin-payment-confirm&id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">
                            Xác nhận lại
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
