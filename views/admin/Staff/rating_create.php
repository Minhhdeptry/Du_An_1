<?php
// ============================================
// FILE 2: views/admin/Staff/rating_create.php
// ============================================
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-star"></i> Đánh giá HDV</h2>
        <a href="index.php?act=admin-booking" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Booking Info -->
    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">📍 Thông tin Booking</h5>
        </div>
        <div class="card-body">
            <p><strong>Mã booking:</strong> <?= htmlspecialchars($booking['booking_code']) ?></p>
            <p><strong>Tour:</strong> <?= htmlspecialchars($booking['tour_name']) ?></p>
            <p><strong>Khách:</strong> <?= htmlspecialchars($booking['contact_name']) ?></p>
        </div>
    </div>

    <!-- Rating Form -->
    <form method="POST" action="index.php?act=admin-staff-rating-store" class="card p-4">
        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

        <div class="mb-3">
            <label class="form-label fw-bold">Chọn HDV <span class="text-danger">*</span></label>
            <select name="staff_id" class="form-select" required>
                <option value="">-- Chọn HDV --</option>
                <?php foreach ($staffs as $s): ?>
                    <option value="<?= $s['id'] ?>">
                        <?= htmlspecialchars($s['full_name']) ?> 
                        (<?= $s['role'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Đánh giá tổng thể <span class="text-danger">*</span></label>
            <div class="d-flex gap-2 mb-2">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <label class="btn btn-outline-warning" style="font-size: 1.5rem;">
                        <input type="radio" name="rating" value="<?= $i ?>" required class="d-none">
                        <span class="star" data-value="<?= $i ?>">☆</span>
                    </label>
                <?php endfor; ?>
            </div>
            <small class="text-muted">Click vào ngôi sao để đánh giá</small>
        </div>

        <!-- Criteria -->
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label">📚 Kiến thức (1-5)</label>
                <input type="number" name="criteria_knowledge" class="form-control" min="1" max="5">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">💬 Giao tiếp (1-5)</label>
                <input type="number" name="criteria_communication" class="form-control" min="1" max="5">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">😊 Thái độ (1-5)</label>
                <input type="number" name="criteria_attitude" class="form-control" min="1" max="5">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">⏰ Đúng giờ (1-5)</label>
                <input type="number" name="criteria_punctuality" class="form-control" min="1" max="5">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Nhận xét</label>
            <textarea name="comment" class="form-control" rows="4" 
                      placeholder="Nhập nhận xét chi tiết về HDV..."></textarea>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Lưu đánh giá
            </button>
            <a href="index.php?act=admin-booking" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
</div>

<script>
// Star rating interactive
document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', function() {
        const value = this.dataset.value;
        const parent = this.closest('.d-flex');
        
        // Update radio
        parent.querySelector(`input[value="${value}"]`).checked = true;
        
        // Update stars display
        parent.querySelectorAll('.star').forEach((s, i) => {
            s.textContent = (i + 1) <= value ? '⭐' : '☆';
        });
    });
});
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">