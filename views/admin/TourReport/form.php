<?php
// ============================================
// FILE 2: views/admin/TourReport/form.php
// ============================================
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-file-text"></i> 
                <?= $existingReport ? 'Sửa' : 'Tạo' ?> Báo cáo Tour
            </h2>
            <p class="text-muted mb-0">
                <strong><?= htmlspecialchars($schedule['tour_title']) ?></strong><br>
                <small>
                    <?= date('d/m/Y', strtotime($schedule['depart_date'])) ?> - 
                    <?= date('d/m/Y', strtotime($schedule['return_date'])) ?>
                </small>
            </p>
        </div>
        <a href="?act=admin-tour-reports" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <form method="POST" action="?act=admin-tour-report-save">
        <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>">

        <!-- Thống kê nhanh -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?= $checkInStats['total_bookings'] ?? 0 ?></h3>
                        <small class="text-muted">Bookings</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success"><?= $checkInStats['checked_in'] ?? 0 ?></h3>
                        <small class="text-muted">Đã check-in</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-danger"><?= $checkInStats['absent'] ?? 0 ?></h3>
                        <small class="text-muted">Vắng mặt</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-people"></i> Thông tin khách thực tế</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Số khách thực tế tham gia <span class="text-danger">*</span>
                    </label>
                    <input type="number" 
                           name="actual_guests" 
                           class="form-control form-control-lg" 
                           min="0" 
                           value="<?= htmlspecialchars($existingReport['actual_guests'] ?? $checkInStats['checked_in'] ?? 0) ?>"
                           required>
                    <small class="text-muted">
                        Ghi nhận số khách thực tế có mặt trong tour
                    </small>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Sự cố & Vấn đề phát sinh</h5>
            </div>
            <div class="card-body">
                <textarea name="incidents" 
                          class="form-control" 
                          rows="5"
                          placeholder="VD: Xe bus bị chậm 30 phút do tắc đường. Một khách bị say xe, đã hỗ trợ thuốc."><?= htmlspecialchars($existingReport['incidents'] ?? '') ?></textarea>
                <small class="text-muted">
                    Ghi chú các sự cố, vấn đề phát sinh trong tour (nếu có)
                </small>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-chat-quote"></i> Phản hồi từ khách hàng</h5>
            </div>
            <div class="card-body">
                <textarea name="customer_feedback" 
                          class="form-control" 
                          rows="5"
                          placeholder="VD: Khách hài lòng về lịch trình. Có ý kiến muốn thêm thời gian tham quan Vịnh Hạ Long."><?= htmlspecialchars($existingReport['customer_feedback'] ?? '') ?></textarea>
                <small class="text-muted">
                    Tổng hợp phản hồi, góp ý từ khách hàng
                </small>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-journal-text"></i> Ghi chú của HDV</h5>
            </div>
            <div class="card-body">
                <textarea name="guide_notes" 
                          class="form-control" 
                          rows="5"
                          placeholder="VD: Nhà hàng phục vụ tốt. Khách sạn cần cải thiện dịch vụ phòng."><?= htmlspecialchars($existingReport['guide_notes'] ?? '') ?></textarea>
                <small class="text-muted">
                    Nhận xét của HDV về tour, đối tác, dịch vụ...
                </small>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cash"></i> Chi phí phát sinh</h5>
            </div>
            <div class="card-body">
                <textarea name="expenses_summary" 
                          class="form-control" 
                          rows="4"
                          placeholder="VD: Thuốc say xe: 50,000đ, Tip tài xế: 200,000đ"><?= htmlspecialchars($existingReport['expenses_summary'] ?? '') ?></textarea>
                <small class="text-muted">
                    Tóm tắt các chi phí phát sinh ngoài dự kiến
                </small>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-star"></i> Đánh giá tổng thể</h5>
            </div>
            <div class="card-body">
                <label class="form-label fw-bold">Chất lượng tour (1-5 sao)</label>
                <div class="d-flex gap-3 mb-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label class="btn btn-outline-warning" style="font-size: 1.5rem;">
                            <input type="radio" 
                                   name="overall_rating" 
                                   value="<?= $i ?>" 
                                   class="d-none"
                                   <?= ($existingReport['overall_rating'] ?? 5) == $i ? 'checked' : '' ?>>
                            <span class="star" data-value="<?= $i ?>">
                                <?= ($existingReport['overall_rating'] ?? 5) >= $i ? '⭐' : '☆' ?>
                            </span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save"></i> Lưu báo cáo
            </button>
            <a href="?act=admin-tour-reports" class="btn btn-secondary btn-lg">
                <i class="bi bi-x-circle"></i> Hủy
            </a>
        </div>
    </form>
</div>

<script>
// Star rating interactive
document.querySelectorAll('.star').forEach(star => {
    star.parentElement.addEventListener('click', function() {
        const value = this.querySelector('input').value;
        
        // Update all stars
        document.querySelectorAll('.star').forEach((s, i) => {
            s.textContent = (i + 1) <= value ? '⭐' : '☆';
        });
    });
});
</script>