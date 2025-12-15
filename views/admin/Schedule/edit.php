<div class="container mt-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">✏️ Sửa lịch khởi hành</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?act=admin-schedule-update">
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
                        <i class="bi bi-info-circle"></i> Tour theo yêu cầu: không giới hạn số lượng khách, phù hợp với
                        tour riêng/private
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

                <!-- Số ghế (ẩn/hiện theo loại tour) -->
                <div class="mb-3" id="seatsSection">
                    <label class="form-label fw-bold">Tổng số ghế <span class="text-danger">*</span></label>
                    <input type="number" name="seats_total" id="seatsTotal" class="form-control" min="1"
                        value="<?= $schedule['seats_total'] ?>">
                    <small class="text-muted">
                        Số lượng chỗ ngồi tối đa cho tour này.
                        <strong>Hiện đang có <?= $schedule['seats_available'] ?> ghế trống.</strong>
                    </small>
                </div>

                <!-- Thông báo Tour không giới hạn -->
                <div class="mb-3 d-none" id="unlimitedNotice">
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-infinity"></i> <strong>Tour không giới hạn số ghế</strong>
                        <br><small>Có thể nhận không giới hạn số lượng khách đặt tour</small>
                    </div>
                </div>

                <!-- Giá -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá người lớn (từ 12 tuổi trwro lên) <span class="text-danger">*</span></label>
                        <input type="number" name="price_adult" class="form-control" min="0"
                            value="<?= $schedule['price_adult'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá trẻ em (từ 12 tuổi từ xuống)</label>
                        <input type="number" name="price_children" class="form-control" min="0"
                            value="<?= $schedule['price_children'] ?>">
                    </div>
                </div>

                <!-- Trạng thái -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="OPEN" <?= $schedule['status'] == 'OPEN' ? 'selected' : '' ?>>Mở đăng ký</option>
                        <option value="CLOSED" <?= $schedule['status'] == 'CLOSED' ? 'selected' : '' ?>>Đã đóng</option>
                        <option value="CANCELED" <?= $schedule['status'] == 'CANCELED' ? 'selected' : '' ?>>Đã hủy</option>
                        <option value="FINISHED" <?= $schedule['status'] == 'FINISHED' ? 'selected' : '' ?>>Hoàn tất
                        </option>
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
    // ✅ Xử lý ẩn/hiện số ghế khi chọn loại tour
    function toggleSeatsSection() {
        const tourType = document.getElementById('tourType').value;
        const seatsSection = document.getElementById('seatsSection');
        const seatsInput = document.getElementById('seatsTotal');
        const unlimitedNotice = document.getElementById('unlimitedNotice');

        if (tourType === 'ON_DEMAND') {
            // Ẩn phần nhập số ghế
            seatsSection.classList.add('d-none');
            seatsInput.removeAttribute('required');
            seatsInput.removeAttribute('min'); // ✅ THÊM: Xóa min validation
            seatsInput.value = '0';

            // Hiện thông báo không giới hạn
            unlimitedNotice.classList.remove('d-none');
        } else {
            // Hiện phần nhập số ghế
            seatsSection.classList.remove('d-none');
            seatsInput.setAttribute('required', 'required');
            seatsInput.setAttribute('min', '1'); // ✅ THÊM: Set lại min

            // Ẩn thông báo không giới hạn
            unlimitedNotice.classList.add('d-none');

            // Nếu giá trị = 0 thì set về 30 mặc định
            if (seatsInput.value == '0') {
                seatsInput.value = '30';
            }
        }
    }

    // Lắng nghe sự kiện thay đổi loại tour
    document.getElementById('tourType').addEventListener('change', toggleSeatsSection);

    // Chạy khi trang load xong
    document.addEventListener('DOMContentLoaded', function () {
        toggleSeatsSection();
    });

    // ✅ THÊM: Debug form submit
    document.querySelector('form').addEventListener('submit', function (e) {
        console.log('Form đang submit...');
        console.log('Tour type:', document.getElementById('tourType').value);
        console.log('Seats total:', document.getElementById('seatsTotal').value);
    });
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">