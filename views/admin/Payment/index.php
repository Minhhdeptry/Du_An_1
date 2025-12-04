<h2 class="mb-4"><?= $title ?></h2>


<table class="table table-striped table-bordered">
    <thead class="thead-dark">
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
            <tr>
                <td><?= $p['id'] ?></td>
                <td>#<?= $p['booking_id'] ?></td>
                <td><?= $p['customer_name'] ?></td>
                <td><?= number_format($p['amount']) ?>đ</td>
                <td><span class="badge badge-info"><?= $p['type'] ?? '-' ?></span></td>
                <td><?= $p['method'] ?></td>
                <td>
                    <?php if ($p['status'] == 'PENDING'): ?>
                        <span class="badge badge-warning">Chờ xác nhận</span>

                    <?php elseif ($p['status'] == 'SUCCESS'): ?>
                        <span class="badge badge-success">Đã thanh toán</span>

                    <?php elseif ($p['status'] == 'FAILED'): ?>
                        <span class="badge badge-danger">Thất bại</span>

                    <?php elseif ($p['status'] == 'REFUNDED'): ?>
                        <span class="badge badge-secondary">Đã hoàn / Đã hủy</span>
                    <?php endif; ?>
                </td>

                <td><?= $p['paid_at'] ?></td>

                <td>
                    <!-- Xem lịch sử -->
                    <a href="index.php?act=admin-payment-history&booking_id=<?= $p['booking_id'] ?>"
                        class="btn btn-info btn-sm mb-1">
                        Lịch sử
                    </a>

                    <?php if ($p['status'] === 'PENDING'): ?>

                        <!-- Nút xác nhận -->
                        <a href="index.php?act=admin-payment-confirm&id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">
                            Xác nhận
                        </a>

                    <?php elseif ($p['status'] === 'SUCCESS'): ?>

                        <!-- Nút hủy xác nhận -->
                        <a href="index.php?act=admin-payment-cancel&id=<?= $p['id'] ?>" class="btn btn-warning btn-sm"
                            onclick="return confirm('Bạn có chắc muốn hủy xác nhận thanh toán này?')">
                            Hủy xác nhận
                        </a>

                    <?php elseif ($p['status'] === 'REFUNDED'): ?>

                        <!-- Xác nhận lại -->
                        <a href="index.php?act=admin-payment-confirm&id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">
                            Xác nhận lại
                        </a>

                    <?php endif; ?>
                </td>



            </tr>
        <?php endforeach; ?>
    </tbody>
</table>