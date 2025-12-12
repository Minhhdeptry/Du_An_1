<?php
// ============================================
// FILE 1: views/admin/CheckIn/today.php
// ============================================
?>
<style>
    .tour-card {
        border-radius: 12px;
        border: 2px solid #e9ecef;
        transition: all 0.3s;
        margin-bottom: 20px;
    }
    .tour-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }
    .checkin-progress {
        height: 8px;
        border-radius: 10px;
    }
    .booking-item {
        border-left: 3px solid #e9ecef;
        padding-left: 15px;
        margin-bottom: 15px;
    }
    .booking-item.checked-in {
        border-left-color: #10b981;
        background: #f0fdf4;
    }
</style>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-check-square"></i> Check-in Tour
            </h2>
            <p class="text-muted mb-0">
                Ngày: <strong><?= date('d/m/Y', strtotime($date)) ?></strong> | 
                <span class="badge bg-primary"><?= count($tourData) ?> tour</span>
            </p>
        </div>
        <div>
            <input type="date" 
                   class="form-control" 
                   value="<?= $date ?>"
                   onchange="window.location='?act=admin-checkin-today&date='+this.value">
        </div>
    </div>

    <!-- Tours List -->
    <?php if (empty($tourData)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">Không có tour nào cần check-in ngày này</h4>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($tourData as $item): ?>
            <?php 
            $schedule = $item['schedule'];
            $bookings = $item['bookings'];
            $stats = $item['stats'];
            
            $totalGuests = $stats['total_guests'] ?? 0;
            $checkedIn = $stats['checked_in'] ?? 0;
            $progress = $totalGuests > 0 ? ($checkedIn / $totalGuests * 100) : 0;
            ?>
            
            <div class="card tour-card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">
                                <span class="badge bg-light text-dark"><?= htmlspecialchars($schedule['tour_code']) ?></span>
                                <?= htmlspecialchars($schedule['tour_title']) ?>
                            </h5>
                            <small>
                                <i class="bi bi-clock"></i> <?= date('H:i', strtotime($schedule['depart_date'])) ?> |
                                <i class="bi bi-person"></i> <?= $totalGuests ?> khách
                            </small>
                        </div>
                        <a href="?act=admin-checkin-report&schedule_id=<?= $schedule['id'] ?>" 
                           class="btn btn-light btn-sm">
                            <i class="bi bi-file-text"></i> Báo cáo
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tiến độ check-in:</span>
                            <strong class="text-primary"><?= $checkedIn ?>/<?= $totalGuests ?> khách</strong>
                        </div>
                        <div class="progress checkin-progress">
                            <div class="progress-bar bg-success" 
                                 style="width: <?= $progress ?>%"></div>
                        </div>
                    </div>

                    <!-- Bookings List -->
                    <?php if (empty($bookings)): ?>
                        <div class="alert alert-info">Chưa có booking nào</div>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                            $isCheckedIn = !empty($booking['checked_in_at']);
                            $itemClass = $isCheckedIn ? 'booking-item checked-in' : 'booking-item';
                            ?>
                            
                            <div class="<?= $itemClass ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <code><?= htmlspecialchars($booking['booking_code']) ?></code>
                                            <?= htmlspecialchars($booking['contact_name']) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-people"></i> 
                                            <?= $booking['adults'] ?> người lớn + <?= $booking['children'] ?> trẻ em
                                        </small>
                                    </div>
                                    
                                    <div>
                                        <?php if ($isCheckedIn): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Đã check-in
                                            </span>
                                            <small class="d-block text-muted mt-1">
                                                <?= date('H:i', strtotime($booking['checked_in_at'])) ?>
                                            </small>
                                            <a href="?act=admin-checkin-undo&booking_id=<?= $booking['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger mt-1"
                                               onclick="return confirm('Hủy check-in?')">
                                                Hủy
                                            </a>
                                        <?php else: ?>
                                            <a href="?act=admin-checkin-guests&booking_id=<?= $booking['id'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-check-square"></i> Check-in
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
