<div class="container mt-4">
    <h2>Chi tiết Booking #<?= htmlspecialchars($booking['booking_code']) ?></h2>
    
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Trạng thái booking:</strong> 
                        <span class="badge bg-primary"><?= $statusText[$booking['status']] ?></span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Trạng thái thanh toán:</strong>
                        <?php
                        $paymentBadge = match($booking['payment_status']) {
                            'FULL_PAID' => '<span class="badge bg-success">💰 Đã thanh toán đầy đủ</span>',
                            'DEPOSIT_PAID' => '<span class="badge bg-info">💵 Đã cọc</span>',
                            default => '<span class="badge bg-warning">⏳ Chưa thanh toán</span>'
                        };
                        echo $paymentBadge;
                        ?>
                    </p>
                </div>
            </div>
            
            <!-- Các thông tin khác -->
            <p><strong>Khách hàng:</strong> <?= htmlspecialchars($booking['contact_name']) ?></p>
            <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($booking['contact_phone']) ?></p>
            <p><strong>Tổng tiền:</strong> <?= number_format($booking['total_amount']) ?>đ</p>
        </div>
    </div>
    
    <!-- Lịch sử thanh toán -->
    <?php if (!empty($items)): ?>
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <h5>💳 Lịch sử thanh toán</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Loại</th>
                        <th>Số tiền</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $payment): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($payment['paid_at'])) ?></td>
                        <td>
                            <span class="badge bg-<?= $payment['type'] == 'FULL' ? 'success' : 'info' ?>">
                                <?= $payment['type'] == 'FULL' ? 'Thanh toán đủ' : 'Đặt cọc' ?>
                            </span>
                        </td>
                        <td><?= number_format($payment['amount']) ?>đ</td>
                        <td><?= $payment['method'] ?></td>
                        <td>
                            <span class="badge bg-<?= $payment['status'] == 'SUCCESS' ? 'success' : 'warning' ?>">
                                <?= $payment['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>