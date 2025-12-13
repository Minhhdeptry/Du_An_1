<!-- views/admin/Booking/create.php -->
<?php
$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">➕ Tạo Booking mới</h2>
        <a href="index.php?act=admin-booking" class="btn btn-secondary">← Quay lại</a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- ✅ CHẾ ĐỘ TẠO BOOKING -->
    <div class="card mb-3 shadow-sm border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">🎯 Chọn loại booking</h5>
        </div>
        <div class="card-body">
            <div class="btn-group w-100 mb-3" role="group">
                <input type="radio" class="btn-check" name="booking_mode" id="mode_scheduled" 
                    value="scheduled" checked onclick="switchMode('scheduled')">
                <label class="btn btn-outline-primary btn-lg" for="mode_scheduled">
                    <i class="bi bi-calendar-check"></i> Đặt theo lịch có sẵn
                    <br><small>Chọn từ các tour đang mở</small>
                </label>

                <input type="radio" class="btn-check" name="booking_mode" id="mode_custom" 
                    value="custom" onclick="switchMode('custom')">
                <label class="btn btn-outline-success btn-lg" for="mode_custom">
                    <i class="bi bi-pencil-square"></i> Tạo tour theo yêu cầu
                    <br><small>Tự do tùy chỉnh thông tin</small>
                </label>
            </div>
        </div>
    </div>

    <form action="index.php?act=admin-booking-store" method="POST" id="bookingForm">
        <!-- =============================================
             🔵 CHẾ ĐỘ 1: ĐẶT THEO LỊCH CÓ SẴN
             ============================================= -->
        <div id="scheduledMode">
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📅 Chọn lịch tour</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lịch khởi hành <span class="text-danger">*</span></label>
                        <select name="tour_schedule_id" id="tour_schedule_select" class="form-select form-select-lg">
                            <option value="">-- Chọn lịch tour --</option>
                            <?php foreach ($schedules as $sc): ?>
                                <?php
                                $tourTitle = htmlspecialchars($sc['tour_title'] ?? '');
                                $category = htmlspecialchars($sc['category_name'] ?? '');
                                $departDate = date('d/m/Y', strtotime($sc['depart_date']));
                                $duration = (int) ($sc['duration_days'] ?? 0);
                                $seatsAvail = (int) ($sc['seats_available'] ?? 0);
                                $priceAdult = (float) ($sc['price_adult'] ?? 0);
                                $priceChildren = (float) ($sc['price_children'] ?? 0);
                                ?>
                                <option value="<?= $sc['id'] ?>" 
                                    data-duration="<?= $duration ?>"
                                    data-price-adult="<?= $priceAdult ?>"
                                    data-price-children="<?= $priceChildren ?>"
                                    data-seats="<?= $seatsAvail ?>">
                                    [<?= $category ?>] <?= $tourTitle ?> - Khởi hành: <?= $departDate ?> (<?= $duration ?> ngày)
                                    - Còn <?= $seatsAvail ?> chỗ
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Giá và thông tin tour sẽ tự động điền
                        </small>
                    </div>

                    <!-- Thông tin tour (auto-fill) -->
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Giá người lớn (VNĐ)</label>
                            <input type="text" id="display_price_adult" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Giá trẻ em (VNĐ)</label>
                            <input type="text" id="display_price_children" class="form-control bg-light" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =============================================
             🟢 CHẾ ĐỘ 2: TẠO TOUR THEO YÊU CẦU
             ============================================= -->
        <div id="customMode" style="display:none;">
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">✏️ Thông tin tour theo yêu cầu</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên tour <span class="text-danger">*</span></label>
                        <input type="text" name="custom_tour_name" id="custom_tour_name" 
                            class="form-control" placeholder="Vd: Tour Phú Quốc 4N3Đ">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày khởi hành <span class="text-danger">*</span></label>
                            <input type="date" name="depart_date" id="custom_depart_date" 
                                class="form-control" min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày về</label>
                            <input type="date" name="return_date" id="custom_return_date" 
                                class="form-control" min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Giá người lớn (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" name="price_adult" id="custom_price_adult" 
                                class="form-control" min="0" step="1000" oninput="updateTotals()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Giá trẻ em (VNĐ)</label>
                            <input type="number" name="price_children" id="custom_price_children" 
                                class="form-control" min="0" step="1000" oninput="updateTotals()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =============================================
             THÔNG TIN KHÁCH HÀNG (CHUNG)
             ============================================= -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-warning">
                <h5 class="mb-0">👤 Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" name="contact_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="contact_phone" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="contact_email" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Người lớn <span class="text-danger">*</span></label>
                        <input type="number" name="adults" id="adults" class="form-control" 
                            min="1" value="1" required oninput="updateTotals()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Trẻ em</label>
                        <input type="number" name="children" id="children" class="form-control" 
                            min="0" value="0" oninput="updateTotals()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tổng người</label>
                        <input type="number" id="total_people" class="form-control bg-light" 
                            value="1" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- YÊU CẦU ĐẶC BIỆT -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">📝 Yêu cầu đặc biệt</h5>
            </div>
            <div class="card-body">
                <textarea name="special_request" class="form-control" rows="3" 
                    placeholder="Ghi chú đặc biệt từ khách hàng..."></textarea>
            </div>
        </div>

        <!-- TỔNG TIỀN -->
        <div class="card mb-3 shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">💵 Tổng thanh toán</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0"><strong>Tiền tour:</strong></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h3 class="mb-0 fw-bold text-danger">
                            <span id="total_amount">0</span> đ
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-check-circle me-2"></i>Tạo booking
            </button>
            <a href="index.php?act=admin-booking" class="btn btn-secondary btn-lg">
                <i class="bi bi-x-circle me-2"></i>Hủy
            </a>
        </div>
    </form>
</div>

<script>
let currentMode = 'scheduled';

function switchMode(mode) {
    currentMode = mode;
    const scheduledMode = document.getElementById('scheduledMode');
    const customMode = document.getElementById('customMode');
    const scheduleSelect = document.getElementById('tour_schedule_select');
    const customFields = ['custom_tour_name', 'custom_depart_date', 'custom_price_adult'];

    if (mode === 'scheduled') {
        scheduledMode.style.display = 'block';
        customMode.style.display = 'none';
        scheduleSelect.required = true;
        customFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.required = false;
        });
    } else {
        scheduledMode.style.display = 'none';
        customMode.style.display = 'block';
        scheduleSelect.required = false;
        scheduleSelect.value = '';
        customFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.required = true;
        });
    }
    updateTotals();
}

function updateTotals() {
    const adults = parseInt(document.getElementById('adults').value || 0);
    const children = parseInt(document.getElementById('children').value || 0);
    document.getElementById('total_people').value = adults + children;

    let priceAdult = 0;
    let priceChildren = 0;

    if (currentMode === 'scheduled') {
        const selected = document.getElementById('tour_schedule_select').selectedOptions[0];
        if (selected && selected.value) {
            priceAdult = parseFloat(selected.dataset.priceAdult || 0);
            priceChildren = parseFloat(selected.dataset.priceChildren || 0);
        }
    } else {
        priceAdult = parseFloat(document.getElementById('custom_price_adult')?.value || 0);
        priceChildren = parseFloat(document.getElementById('custom_price_children')?.value || 0);
    }

    const total = (adults * priceAdult) + (children * priceChildren);
    document.getElementById('total_amount').textContent = total.toLocaleString('vi-VN');
}

// Event: Chọn tour schedule
document.getElementById('tour_schedule_select').addEventListener('change', function() {
    const selected = this.selectedOptions[0];
    if (selected && selected.value) {
        const priceAdult = parseFloat(selected.dataset.priceAdult || 0);
        const priceChildren = parseFloat(selected.dataset.priceChildren || 0);
        
        document.getElementById('display_price_adult').value = priceAdult.toLocaleString('vi-VN') + ' đ';
        document.getElementById('display_price_children').value = priceChildren.toLocaleString('vi-VN') + ' đ';
        
        updateTotals();
    }
});

// Initialize
updateTotals();
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">