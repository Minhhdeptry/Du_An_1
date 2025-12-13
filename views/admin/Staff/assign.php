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

<script>
    // ✅ THÊM VÀO FILE: views/admin/Staff/assign.php (trong thẻ <script>)

    document.addEventListener('DOMContentLoaded', function () {
        const guideSelect = document.querySelector('select[name="guide_id"]');
        const assistantSelect = document.querySelector('select[name="assistant_guide_id"]');

        // ✅ Khi chọn HDV chính → Loại bỏ khỏi danh sách HDV phụ
        guideSelect.addEventListener('change', function () {
            const selectedGuideId = this.value;
            updateAssistantOptions(selectedGuideId);
        });

        // ✅ Khi chọn HDV phụ → Loại bỏ khỏi danh sách HDV chính
        assistantSelect.addEventListener('change', function () {
            const selectedAssistantId = this.value;
            updateGuideOptions(selectedAssistantId);
        });

        function updateAssistantOptions(excludeId) {
            // Lưu giá trị hiện tại của HDV phụ
            const currentValue = assistantSelect.value;

            // Reset tất cả options
            Array.from(assistantSelect.options).forEach(option => {
                option.disabled = false;
                option.style.display = '';
            });

            // Disable option trùng với HDV chính
            if (excludeId) {
                const optionToDisable = assistantSelect.querySelector(`option[value="${excludeId}"]`);
                if (optionToDisable) {
                    optionToDisable.disabled = true;
                    optionToDisable.style.display = 'none';
                }

                // Nếu HDV phụ đang chọn = HDV chính → Clear
                if (currentValue === excludeId) {
                    assistantSelect.value = '';
                }
            }
        }

        function updateGuideOptions(excludeId) {
            // Lưu giá trị hiện tại của HDV chính
            const currentValue = guideSelect.value;

            // Reset tất cả options
            Array.from(guideSelect.options).forEach(option => {
                option.disabled = false;
                option.style.display = '';
            });

            // Disable option trùng với HDV phụ
            if (excludeId) {
                const optionToDisable = guideSelect.querySelector(`option[value="${excludeId}"]`);
                if (optionToDisable) {
                    optionToDisable.disabled = true;
                    optionToDisable.style.display = 'none';
                }

                // Nếu HDV chính đang chọn = HDV phụ → Clear
                if (currentValue === excludeId) {
                    guideSelect.value = '';
                }
            }
        }

        // ✅ Khởi tạo ban đầu
        const initialGuide = guideSelect.value;
        const initialAssistant = assistantSelect.value;

        if (initialGuide) updateAssistantOptions(initialGuide);
        if (initialAssistant) updateGuideOptions(initialAssistant);
    });
</script>