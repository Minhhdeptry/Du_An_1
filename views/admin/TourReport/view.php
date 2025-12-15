<?php
// ============================================
// FILE 3: views/admin/TourReport/view.php
// ============================================
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-file-text"></i> Chi tiết Báo cáo Tour
            </h2>
            <p class="text-muted mb-0">
                <small>
                    Báo cáo được tạo: <?= date('d/m/Y H:i', strtotime($report['created_at'])) ?>
                    bởi <?= htmlspecialchars($report['created_by_name'] ?? 'System') ?>
                </small>
            </p>
        </div>
        <div>
            <a href="?act=admin-tour-report-create&schedule_id=<?= $report['schedule_id'] ?>" 
               class="btn btn-warning">
                <i class="bi bi-pencil"></i> Sửa
            </a>
            <a href="?act=admin-tour-reports-all" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Tour Info -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-info-circle"></i> Thông tin Tour</h5>
        </div>
        <div class="card-body">
            <h4><?= htmlspecialchars($report['tour_name']) ?></h4>
            <div class="row mt-3">
                <div class="col-md-4">
                    <small class="text-muted">Ngày khởi hành:</small><br>
                    <strong><?= date('d/m/Y', strtotime($report['depart_date'])) ?></strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">HDV dẫn tour:</small><br>
                    <strong><?= htmlspecialchars($report['guide_name'] ?? 'N/A') ?></strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Đánh giá tổng thể:</small><br>
                    <span class="text-warning" style="font-size: 1.3rem;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= $i <= ($report['overall_rating'] ?? 0) ? '⭐' : '☆' ?>
                        <?php endfor; ?>
                        <strong class="text-dark"><?= number_format($report['overall_rating'], 1) ?>/5</strong>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Actual Guests -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-people"></i> Khách thực tế</h5>
        </div>
        <div class="card-body text-center">
            <div class="display-4 text-success fw-bold">
                <?= $report['actual_guests'] ?? 0 ?>
            </div>
            <p class="text-muted mb-0">khách tham gia</p>
        </div>
    </div>

    <!-- Incidents -->
    <?php if (!empty($report['incidents'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Sự cố & Vấn đề</h5>
            </div>
            <div class="card-body">
                <?= nl2br(htmlspecialchars($report['incidents'])) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Customer Feedback -->
    <?php if (!empty($report['customer_feedback'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-chat-quote"></i> Phản hồi khách hàng</h5>
            </div>
            <div class="card-body">
                <?= nl2br(htmlspecialchars($report['customer_feedback'])) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Guide Notes -->
    <?php if (!empty($report['guide_notes'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-journal-text"></i> Ghi chú HDV</h5>
            </div>
            <div class="card-body">
                <?= nl2br(htmlspecialchars($report['guide_notes'])) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Expenses -->
    <?php if (!empty($report['expenses_summary'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cash"></i> Chi phí phát sinh</h5>
            </div>
            <div class="card-body">
                <?= nl2br(htmlspecialchars($report['expenses_summary'])) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Print Button -->
    <div class="mt-4">
        <button class="btn btn-success" onclick="window.print()">
            <i class="bi bi-printer"></i> In báo cáo
        </button>
    </div>
</div>