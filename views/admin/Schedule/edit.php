<div class="container mt-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">✏️ Sửa lịch khởi hành</h5>
        </div>
        <div class="card-body">
            <form method="post" action="index.php?act=admin-schedule-update">
                <input type="hidden" name="id" value="<?= $schedule['id'] ?>">
                
                <!-- Chọn Tour -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Tour <span class="text-danger">*</span></label>
                    <select name="tour_id" class="form-select" required>
                        <option value="">-- Chọn tour --</option>
                        <?php foreach ($tours as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $t['id'] == $schedule['tour_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['title']) ?> (<?= htmlspecialchars($t['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Loại Tour -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Loại tour <span class="text-danger">*</span></label>
                    <select name="tour_type" id="tourType" class="form-select" required>
                        <option value="REGULAR" <?= ($schedule['tour_type'] ?? 'REGULAR') == 'REGULAR' ? 'selected' : '' ?>>
                            Tour thường (có giới hạn ghế)
                        </option>
                        <option value="ON_DEMAND" <?= ($schedule['tour_type'] ?? 'REGULAR') == 'ON_DEMAND' ? 'selected' : '' ?>>
                            Tour theo yêu cầu (không giới hạn ghế)
                        </option>
                    </select>
                    <small class="text-muted">
                        Tour theo yêu cầu: không giới hạn số lượng khách, phù hợp với tour riêng/private
                    </small>
                </div>

                <!-- Ngày đi & về -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày khởi hành <span class="text-danger">*</span></label>
                        <input type="date" name="depart_date" class="form-control" 
                               value="<?= $schedule['depart_date'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày về <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" class="form-control" 
                               value="<?= $schedule['return_date'] ?>" required>
                    </div>
                </div>

                <!-- Số ghế (chỉ hiện với tour thường) -->
                <div class="mb-3" id="seatsSection">
                    <label class="form-label fw-bold">Tổng số ghế <span class="text-danger">*</span></label>
                    <input type="number" name="seats_total" id="seatsTotal" class="form-control" 
                           min="1" value="<?= $schedule['seats_total'] ?>">
                    <small class="text-muted">
                        Số lượng chỗ ngồi tối đa cho tour này. 
                        <strong>Hiện đang có <?= $schedule['seats_available'] ?> ghế trống.</strong>
                    </small>
                </div>

                <!-- Giá -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá người lớn (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="price_adult" class="form-control" 
                               min="0" value="<?= $schedule['price_adult'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá trẻ em (VNĐ)</label>
                        <input type="number" name="price_children" class="form-control" 
                               min="0" value="<?= $schedule['price_children'] ?>">
                    </div>
                </div>

                <!-- Trạng thái -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="OPEN" <?= $schedule['status'] == 'OPEN' ? 'selected' : '' ?>>Mở đăng ký</option>
                        <option value="CLOSED" <?= $schedule['status'] == 'CLOSED' ? 'selected' : '' ?>>Đã đóng</option>
                        <option value="CANCELED" <?= $schedule['status'] == 'CANCELED' ? 'selected' : '' ?>>Đã hủy</option>
                        <option value="FINISHED" <?= $schedule['status'] == 'FINISHED' ? 'selected' : '' ?>>Hoàn tất</option>
                    </select>
                </div>

                <!-- Ghi chú -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="3" 
                              placeholder="Ghi chú thêm về lịch tour này..."><?= htmlspecialchars($schedule['note'] ?? '') ?></textarea>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Cập nhật
                    </button>
                    <a href="index.php?act=admin-schedule" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Ẩn/hiện phần số ghế dựa vào loại tour
document.getElementById('tourType').addEventListener('change', function() {
    const seatsSection = document.getElementById('seatsSection');
    const seatsInput = document.getElementById('seatsTotal');
    
    if (this.value === 'ON_DEMAND') {
        seatsSection.style.display = 'none';
        seatsInput.removeAttribute('required');
    } else {
        seatsSection.style.display = 'block';
        seatsInput.setAttribute('required', 'required');
    }
});

// Trigger khi load trang
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('tourType').dispatchEvent(new Event('change'));
});
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">