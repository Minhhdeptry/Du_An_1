<div class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-3">✏️ Lịch sử đặt tour: <?= htmlspecialchars($user['full_name']) ?></h2>
        <a href="index.php?act=admin-user" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>STT</th>
                <th>Mã đặt tour</th>
                <th>Tên tour</th>
                <th>Ngày khởi hành</th>
                <th>Số người</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($bookings)): ?>
                <?php foreach ($bookings as $i => $b): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($b['booking_code']) ?></td>
                        <td><?= htmlspecialchars($b['tour_name']) ?></td>
                        <td><?= htmlspecialchars($b['depart_date']) ?></td>
                        <td><?= htmlspecialchars($b['total_people'] ?? '-') ?></td>
                        <td><?= isset($b['total_amount']) ? number_format($b['total_amount']) . " đ" : '-' ?></td>
                        <td><?= htmlspecialchars($b['status'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">Chưa có lịch sử đặt tour</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
