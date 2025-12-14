<?php
// ============================================
// FILE 1: views/admin/TourReport/pending.php
// ============================================
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-file-earmark-text"></i> Tour cần làm báo cáo
            </h2>
            <p class="text-muted mb-0">
                Tổng: <strong><?= count($tours) ?></strong> tour hoàn thành chưa có báo cáo
            </p>
        </div>
        <a href="?act=admin-tour-reports-all" class="btn btn-primary">
            <i class="bi bi-list"></i> Xem tất cả báo cáo
        </a>
    </div>

    <?php if (empty($tours)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-check-circle" style="font-size: 4rem; color: #10b981;"></i>
                <h4 class="mt-3 text-muted">Tất cả tour đều đã có báo cáo!</h4>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($tours as $tour): ?>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <span class="badge bg-dark"><?= htmlspecialchars($tour['tour_code']) ?></span>
                                <?= htmlspecialchars($tour['tour_title']) ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted">Ngày khởi hành:</small><br>
                                <strong><?= date('d/m/Y', strtotime($tour['depart_date'])) ?></strong>
                                -
                                <strong><?= date('d/m/Y', strtotime($tour['return_date'])) ?></strong>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted">HDV:</small><br>
                                <strong><?= htmlspecialchars($tour['guide_name'] ?? 'Chưa phân công') ?></strong>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">Thống kê:</small><br>
                                <span class="badge bg-info"><?= $tour['total_bookings'] ?? 0 ?> bookings</span>
                                <span class="badge bg-success"><?= $tour['total_customers'] ?? 0 ?> khách</span>
                            </div>
                            
                            <a href="?act=admin-tour-report-create&schedule_id=<?= $tour['id'] ?>" 
                               class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle"></i> Tạo báo cáo
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
