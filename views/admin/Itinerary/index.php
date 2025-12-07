<style>
    .itinerary-card {
        transition: transform 0.2s;
    }
    .itinerary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-calendar-week text-primary"></i> 
                Lịch trình: <?= htmlspecialchars($tour['title']) ?>
            </h2>
            <p class="text-muted mb-0">
                <i class="bi bi-clock"></i> Tour <?= $tour['duration_days'] ?> ngày
                <?php if (!empty($itineraries)): ?>
                    | <span class="badge bg-success"><?= count($itineraries) ?> ngày đã tạo</span>
                <?php endif; ?>
            </p>
        </div>
        <div>
            <a href="index.php?act=admin-itinerary-create&tour_id=<?= $tour['id'] ?>" 
               class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm ngày mới
            </a>
            <a href="index.php?act=admin-itinerary-list" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Thông báo -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Nội dung -->
    <?php if (empty($itineraries)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">Chưa có lịch trình nào</h4>
                <p class="text-muted mb-4">Hãy thêm ngày đầu tiên cho tour này!</p>
                <a href="index.php?act=admin-itinerary-create&tour_id=<?= $tour['id'] ?>" 
                   class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tạo ngày đầu tiên
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($itineraries as $item): ?>
                <div class="col-12 mb-3">
                    <div class="card itinerary-card shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-geo-alt-fill"></i> 
                                <strong>Ngày <?= $item['day_number'] ?>:</strong> 
                                <?= htmlspecialchars($item['title']) ?>
                            </h5>
                            <div>
                                <a href="index.php?act=admin-itinerary-edit&id=<?= $item['id'] ?>" 
                                   class="btn btn-sm btn-warning text-white">
                                    <i class="bi bi-pencil"></i> Sửa
                                </a>
                                <a href="index.php?act=admin-itinerary-delete&id=<?= $item['id'] ?>&tour_id=<?= $tour['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('⚠️ Xóa lịch trình ngày <?= $item['day_number'] ?>?')">
                                    <i class="bi bi-trash"></i> Xóa
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($item['description'])): ?>
                                <div class="mb-3">
                                    <p class="text-muted mb-1"><i class="bi bi-info-circle"></i> <strong>Tổng quan:</strong></p>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['activities'])): ?>
                                <div class="mb-3">
                                    <p class="text-primary mb-2"><i class="bi bi-list-check"></i> <strong>Hoạt động chi tiết:</strong></p>
                                    <div class="bg-light p-3 rounded" style="white-space: pre-line; font-size: 0.95rem;">
<?= htmlspecialchars($item['activities']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="row mt-3">
                                <?php if (!empty($item['accommodation'])): ?>
                                    <div class="col-md-6 mb-2">
                                        <span class="badge bg-info text-dark" style="font-size: 0.9rem; padding: 8px 12px;">
                                            <i class="bi bi-house-door"></i> <strong>Nghỉ:</strong> <?= htmlspecialchars($item['accommodation']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['meals'])): ?>
                                    <div class="col-md-6 mb-2">
                                        <span class="badge bg-success" style="font-size: 0.9rem; padding: 8px 12px;">
                                            <i class="bi bi-cup-straw"></i> <strong>Bữa ăn:</strong> <?= htmlspecialchars($item['meals']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer text-muted small">
                            <i class="bi bi-clock-history"></i> 
                            Cập nhật: <?= date('d/m/Y H:i', strtotime($item['updated_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Thống kê -->
        <div class="alert alert-info mt-4">
            <i class="bi bi-info-circle-fill"></i> 
            <strong>Tổng kết:</strong> Tour này có <strong><?= count($itineraries) ?> ngày</strong> trong lịch trình.
            <?php if (count($itineraries) < $tour['duration_days']): ?>
                <br><small>⚠️ Tour dự kiến <?= $tour['duration_days'] ?> ngày, còn thiếu <?= $tour['duration_days'] - count($itineraries) ?> ngày.</small>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<script>
// Auto dismiss alerts sau 5s
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>