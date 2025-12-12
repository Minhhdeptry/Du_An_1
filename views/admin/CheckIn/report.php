<?php
// ============================================
// FILE 3: views/admin/CheckIn/report.php
// ============================================
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-file-text"></i> Báo cáo Check-in
            </h2>
            <p class="text-muted mb-0">
                <strong><?= htmlspecialchars($schedule['tour_title']) ?></strong><br>
                <small>
                    <?= date('d/m/Y', strtotime($schedule['depart_date'])) ?> - 
                    <?= date('d/m/Y', strtotime($schedule['return_date'])) ?>
                </small>
            </p>
        </div>
        <a href="?act=admin-checkin-today" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?= $stats['total_bookings'] ?? 0 ?></h3>
                    <small class="text-muted">Tổng booking</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info"><?= $stats['total_guests'] ?? 0 ?></h3>
                    <small class="text-muted">Tổng khách</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?= $stats['checked_in'] ?? 0 ?></h3>
                    <small class="text-muted">Đã check-in</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger"><?= $stats['absent'] ?? 0 ?></h3>
                    <small class="text-muted">Vắng mặt</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed List -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Booking</th>
                        <th>Khách hàng</th>
                        <th>Số người</th>
                        <th>Đã check-in</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($checkInData as $item): ?>
                        <?php 
                        $booking = $item['booking'];
                        $checkIn = $item['check_in'];
                        ?>
                        <tr>
                            <td>
                                <code><?= htmlspecialchars($booking['booking_code']) ?></code>
                            </td>
                            <td><?= htmlspecialchars($booking['contact_name']) ?></td>
                            <td><?= $booking['total_people'] ?></td>
                            <td>
                                <?php if ($checkIn): ?>
                                    <strong class="text-success">
                                        <?= count($checkIn) ?> / <?= $booking['total_people'] ?>
                                    </strong>
                                <?php else: ?>
                                    <span class="text-muted">0 / <?= $booking['total_people'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($checkIn): ?>
                                    <span class="badge bg-success">Đã check-in</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Chưa check-in</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?act=admin-checkin-guests&booking_id=<?= $booking['id'] ?>" 
                                   class="btn btn-sm btn-primary">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Export Button -->
    <div class="mt-4">
        <button class="btn btn-success" onclick="window.print()">
            <i class="bi bi-printer"></i> In báo cáo
        </button>
        <button class="btn btn-primary">
            <i class="bi bi-file-excel"></i> Xuất Excel
        </button>
    </div>
</div>