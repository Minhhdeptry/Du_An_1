<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">➕ Thêm Lịch khởi hành mới</h2>
        <a href="index.php?act=admin-schedule" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>
    
    <form method="POST" action="index.php?act=admin-schedule-store" class="card p-4 shadow-sm">
        
        <!-- Chọn Tour -->
        <div class="form-group mb-3">
            <label class="fw-bold">Tour <span class="text-danger">*</span></label>
            <select name="tour_id" class="form-control" required>
                <option value="">-- Chọn tour --</option>
                <?php foreach ($tours as $t): ?>
                    <option value="<?= $t['id'] ?>">
                        <?= htmlspecialchars($t['title']) ?>
                        (<?= htmlspecialchars($t['category_name'] ?? 'Chưa có') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Loại Tour -->
        <div class="form-group mb-3">
            <label class="fw-bold">Loại tour <span class="text-danger">*</span></label>
            <select name="tour_type" id="tourType" class="form-control" required>
                <option value="REGULAR">Tour thường (có giới hạn ghế)</option>
                <option value="ON_DEMAND">Tour theo yêu cầu (không giới hạn ghế)</option>
            </select>
            <small class="text-muted">
                Tour theo yêu cầu: không giới hạn số lượng khách, phù hợp với tour riêng/private
            </small>
        </div>

        <!-- Ngày đi & về -->
        <div class="form-row mb-3">
            <div class="col-md-6">
                <label class="fw-bold">Ngày đi <span class="text-danger">*</span></label>
                <input type="date" name="depart_date" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="fw-bold">Ngày về <span class="text-danger">*</span></label>
                <input type="date" name="return_date" class="form-control" required>
            </div>
        </div>

        <!-- Số ghế (chỉ hiện với tour thường) -->
        <div class="form-group mb-3" id="seatsSection">
            <label class="fw-bold">Tổng số ghế <span class="text-danger">*</span></label>
            <input type="number" name="seats_total" id="seatsTotal" class="form-control" min="1" value="30">
            <small class="text-muted">Số lượng chỗ ngồi tối đa cho tour này</small>
        </div>

        <!-- Giá -->
        <div class="form-row mb-3">
            <div class="col-md-6">
                <label class="fw-bold">Giá người lớn (VNĐ) <span class="text-danger">*</span></label>
                <input type="number" name="price_adult" class="form-control" min="0" required>
            </div>
            <div class="col-md-6">
                <label class="fw-bold">Giá trẻ em (dưới 10 tuổi) (VNĐ)</label>
                <input type="number" name="price_children" class="form-control" min="0" value="0">
            </div>
        </div>

        <!-- Trạng thái -->
        <div class="form-group mb-3">
            <label class="fw-bold">Trạng thái <span class="text-danger">*</span></label>
            <select name="status" class="form-control" required>
                <option value="OPEN">Mở đăng ký</option>
                <option value="CLOSED">Đã đóng</option>
                <option value="CANCELED">Đã hủy</option>
                <option value="FINISHED">Hoàn tất</option>
            </select>
        </div>

        <!-- Ghi chú -->
        <div class="form-group mb-3">
            <label class="fw-bold">Ghi chú</label>
            <textarea name="note" class="form-control" rows="3" placeholder="Ghi chú thêm về lịch tour này..."></textarea>
        </div>

        <!-- Buttons -->
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Tạo lịch
            </button>
            <a href="index.php?act=admin-schedule" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Hủy
            </a>
        </div>
    </form>
</div>

<script>
// Ẩn/hiện phần số ghế dựa vào loại tour
document.getElementById('tourType').addEventListener('change', function() {
    const seatsSection = document.getElementById('seatsSection');
    const seatsInput = document.getElementById('seatsTotal');
    
    if (this.value === 'ON_DEMAND') {
        seatsSection.style.display = 'none';
        seatsInput.removeAttribute('required');
        seatsInput.value = '0';
    } else {
        seatsSection.style.display = 'block';
        seatsInput.setAttribute('required', 'required');
        seatsInput.value = '30';
    }
});

// Trigger khi load trang
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('tourType').dispatchEvent(new Event('change'));
});
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">