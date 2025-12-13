<?php
// ============================================
// FILE: views/admin/Staff/performance.php
// ============================================
?>

<style>
.stat-card {
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.stat-value {
    font-size: 2.5rem;
    font-weight: bold;
}
</style>

<div class="container-fluid mt-4">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-graph-up-arrow"></i> 
                Hiệu suất HDV: <?= htmlspecialchars($staff['full_name']) ?>
            </h2>
            <p class="text-muted mb-0">
                <span class="badge bg-<?= $staff['status'] == 'ACTIVE' ? 'success' : 'secondary' ?>">
                    <?= $staff['status'] ?>
                </span>
                | <?= $staff['staff_type'] ?>
                | ⭐ <?= number_format($staff['rating'] ?? 0, 1) ?>
            </p>
        </div>
        <div>
            <a href="index.php?act=admin-staff" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-primary text-white">
                <div class="stat-value"><?= $performance['total_tours'] ?? 0 ?></div>
                <div>Tổng số tour</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card bg-success text-white">
                <div class="stat-value"><?= $performance['completed_tours'] ?? 0 ?></div>
                <div>Hoàn thành</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card bg-warning text-dark">
                <div class="stat-value"><?= number_format($performance['total_customers_served'] ?? 0) ?></div>
                <div>Khách đã phục vụ</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card bg-info text-white">
                <div class="stat-value">
                    <?= number_format($performance['avg_performance'] ?? 0, 1) ?>/5
                </div>
                <div>Đánh giá TB</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tour sắp tới -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event"></i> Tour sắp tới (<?= count($upcoming) ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($upcoming)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                            <p class="mt-2">Không có tour nào trong thời gian tới</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($upcoming as $tour): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <span class="badge bg-info"><?= htmlspecialchars($tour['tour_code']) ?></span>
                                                <?= htmlspecialchars($tour['tour_name']) ?>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar"></i> 
                                                <?= date('d/m/Y', strtotime($tour['depart_date'])) ?> 
                                                - 
                                                <?= date('d/m/Y', strtotime($tour['return_date'])) ?>
                                            </small>
                                            <br>
                                            <small>
                                                <i class="bi bi-people"></i> 
                                                <?= $tour['total_bookings'] ?? 0 ?> booking
                                            </small>
                                        </div>
                                        <span class="badge bg-<?= $tour['role'] == 'GUIDE' ? 'primary' : 'success' ?>">
                                            <?= $tour['role'] ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Lịch sử tour gần đây -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Lịch sử tour gần đây
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($history)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-journal-x" style="font-size: 3rem;"></i>
                            <p class="mt-2">Chưa có lịch sử tour</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($history as $h): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($h['tour_code']) ?></span>
                                            <strong><?= htmlspecialchars($h['tour_name']) ?></strong>
                                        </div>
                                        <span class="badge bg-<?= $h['status'] == 'COMPLETED' ? 'success' : 'warning' ?>">
                                            <?= $h['status'] ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i> 
                                        <?= date('d/m/Y', strtotime($h['depart_date'])) ?>
                                    </small>
                                    
                                    <?php if ($h['performance_rating']): ?>
                                        <div class="mt-1">
                                            <span class="text-warning">
                                                ⭐ <?= number_format($h['performance_rating'], 1) ?>/5
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($h['customer_feedback']): ?>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                💬 <?= htmlspecialchars(substr($h['customer_feedback'], 0, 100)) ?>
                                                <?= strlen($h['customer_feedback']) > 100 ? '...' : '' ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Thao tác nhanh</h5>
                    <div class="btn-group" role="group">
                        <a href="index.php?act=admin-staff-cert&staff_id=<?= $staff['id'] ?>" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-award"></i> Quản lý chứng chỉ
                        </a>
                        <a href="index.php?act=admin-staff-rating&staff_id=<?= $staff['id'] ?>" 
                           class="btn btn-outline-warning">
                            <i class="bi bi-star"></i> Xem đánh giá
                        </a>
                        <a href="index.php?act=admin-staff-edit&id=<?= $staff['id'] ?>" 
                           class="btn btn-outline-secondary">
                            <i class="bi bi-pencil"></i> Sửa thông tin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">