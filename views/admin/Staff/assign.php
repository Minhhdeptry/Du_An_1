<?php
// ============================================
// FILE 4: views/admin/Staff/assign.php
// ============================================
?>

<div class="container mt-4">
    <h2>👥 Phân công HDV - <?= htmlspecialchars($schedule['tour_title']) ?></h2>
    <p class="text-muted">Khởi hành: <?= date('d/m/Y', strtotime($schedule['depart_date'])) ?></p>

    <!-- ✅ HIỂN THỊ THÔNG BÁO -->
    <?php if (isset($_SESSION['success'])): ?>
        <?= $_SESSION['success'] ?>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['warning'])): ?>
        <?= $_SESSION['warning'] ?>
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="index.php?act=admin-staff-assign-store" method="POST" class="card p-4 mt-3">
        <input type="hidden" name="tour_schedule_id" value="<?= $schedule['id'] ?>">

        <div class="form-group mb-3">
            <label class="fw-bold">Hướng dẫn viên chính</label>
            <select name="guide_id" class="form-select">
                <option value="">-- Chọn HDV chính --</option>
                <?php foreach ($available_staffs as $s): ?>
                    <option value="<?= $s['id'] ?>">
                        <?= htmlspecialchars($s['full_name']) ?> 
                        (<?= $s['staff_type'] ?>) 
                        <?= $s['rating'] ? '⭐ ' . number_format($s['rating'], 1) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold">Hướng dẫn viên phụ (tùy chọn)</label>
            <select name="assistant_guide_id" class="form-select">
                <option value="">-- Không cần HDV phụ --</option>
                <?php foreach ($available_staffs as $s): ?>
                    <option value="<?= $s['id'] ?>">
                        <?= htmlspecialchars($s['full_name']) ?> 
                        (<?= $s['staff_type'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">✅ Phân công</button>
            <a href="index.php?act=admin-schedule" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
</div>