<?php
// ============================================
// FILE 1: views/admin/Staff/ratings.php
// ============================================
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2>
                <i class="bi bi-star-fill text-warning"></i> 
                Đánh giá - <?= htmlspecialchars($staff['full_name']) ?>
            </h2>
            <p class="text-muted mb-0">
                Đánh giá trung bình: 
                <span class="text-warning fs-5">
                    ⭐ <?= number_format($staff['rating'] ?? 0, 1) ?>/5
                </span>
            </p>
        </div>
        <a href="index.php?act=admin-staff" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h3><?= $statistics['total_ratings'] ?? 0 ?></h3>
                    <small>Tổng đánh giá</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h3><?= $statistics['positive_ratings'] ?? 0 ?></h3>
                    <small>Đánh giá tốt (≥4★)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-warning text-dark">
                <div class="card-body">
                    <h3><?= number_format($statistics['avg_knowledge'] ?? 0, 1) ?></h3>
                    <small>Kiến thức TB</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <h3><?= number_format($statistics['avg_communication'] ?? 0, 1) ?></h3>
                    <small>Giao tiếp TB</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Ratings List -->
    <?php if (empty($ratings)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-star" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">Chưa có đánh giá nào</h4>
                <p class="text-muted">Đánh giá sẽ hiển thị sau khi tour hoàn thành</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📝 Danh sách đánh giá (<?= count($ratings) ?>)</h5>
            </div>
            <div class="card-body">
                <?php foreach ($ratings as $r): ?>
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <span class="badge bg-info"><?= htmlspecialchars($r['booking_code']) ?></span>
                                    <?= htmlspecialchars($r['customer_name'] ?? 'Khách hàng') ?>
                                </h6>
                                <div class="text-warning mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?= $i <= $r['rating'] ? '⭐' : '☆' ?>
                                    <?php endfor; ?>
                                    <strong><?= number_format($r['rating'], 1) ?>/5</strong>
                                </div>
                            </div>
                            <small class="text-muted">
                                <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
                            </small>
                        </div>

                        <?php if ($r['comment']): ?>
                            <p class="mb-2">
                                <i class="bi bi-chat-quote text-muted"></i>
                                "<?= htmlspecialchars($r['comment']) ?>"
                            </p>
                        <?php endif; ?>

                        <!-- Criteria breakdown -->
                        <?php if ($r['criteria_knowledge'] || $r['criteria_communication'] || $r['criteria_attitude'] || $r['criteria_punctuality']): ?>
                            <div class="row g-2">
                                <?php if ($r['criteria_knowledge']): ?>
                                    <div class="col-6 col-md-3">
                                        <small class="text-muted">📚 Kiến thức:</small>
                                        <strong><?= $r['criteria_knowledge'] ?>/5</strong>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($r['criteria_communication']): ?>
                                    <div class="col-6 col-md-3">
                                        <small class="text-muted">💬 Giao tiếp:</small>
                                        <strong><?= $r['criteria_communication'] ?>/5</strong>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($r['criteria_attitude']): ?>
                                    <div class="col-6 col-md-3">
                                        <small class="text-muted">😊 Thái độ:</small>
                                        <strong><?= $r['criteria_attitude'] ?>/5</strong>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($r['criteria_punctuality']): ?>
                                    <div class="col-6 col-md-3">
                                        <small class="text-muted">⏰ Đúng giờ:</small>
                                        <strong><?= $r['criteria_punctuality'] ?>/5</strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
