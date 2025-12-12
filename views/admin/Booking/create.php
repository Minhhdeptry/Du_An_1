<?php
$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">➕ Tạo Booking (Tour theo yêu cầu khách)</h2>
        <a href="index.php?act=admin-booking" class="btn btn-secondary">
            ← Quay lại
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="index.php?act=admin-booking-store" method="POST" id="bookingForm">
        
        <!-- BƯỚC 1: CHỌN TOUR -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">🎯 Bước 1: Chọn Tour</h5>
            </div>
            <div class="card-body">
                
                <!-- Toggle Mode -->
                <div class="mb-3">
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="tour_mode" id="mode_existing" 
                               value="existing" checked onclick="switchMode('existing')">
                        <label class="btn btn-outline-primary" for="mode_existing">
                            <i class="bi bi-list-ul"></i> Chọn tour có sẵn
                        </label>

                        <input type="radio" class="btn-check" name="tour_mode" id="mode_custom" 
                               value="custom" onclick="switchMode('custom')">
                        <label class="btn btn-outline-success" for="mode_custom">
                            <i class="bi bi-pencil-square"></i> Nhập tour mới
                        </label>
                    </div>
                </div>

                <!-- Mode 1: Chọn tour có sẵn -->
                <div id="existingTourSection">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn lịch tour <span class="text-danger">*</span></label>
                        <select name="tour_schedule_id" id="tour_schedule" class="form-select" required>
                            <option value="">-- Chọn lịch tour --</option>
                            <?php foreach ($schedules as $sc): ?>
                                <?php
                                $tourTitle = htmlspecialchars($sc['tour_title'] ?? 'Tên tour chưa có');
                                $category = htmlspecialchars($sc['category_name'] ?? 'Tour');
                                $departDate = isset($sc['depart_date']) ? date('d/m/Y', strtotime($sc['depart_date'])) : '';
                                $duration = (int) ($sc['duration_days'] ?? 0);
                                $priceAdult = (float) ($sc['price_adult'] ?? 0);
                                $priceChildren = (float) ($sc['price_children'] ?? 0);
                                ?>
                                <option value="<?= $sc['id'] ?>" 
                                    data-duration="<?= $duration ?>"
                                    data-price-adult="<?= $priceAdult ?>"
                                    data-price-children="<?= $priceChildren ?>"
                                    <?= ($_SESSION['old_data']['tour_schedule_id'] ?? '') == $sc['id'] ? 'selected' : '' ?>>
                                    [<?= $category ?>] <?= $tourTitle ?> (<?= $departDate ?> - <?= $duration ?> ngày)
                                    - <?= $priceAdult ?> VNĐ/người lớn, <?= $priceChildren ?> VNĐ/trẻ em
                                </option>

                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <!-- Mode 2: Nhập tour mới -->
                <div id="customTourSection" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên tour theo yêu cầu <span class="text-danger">*</span></label>
                        <input type="text" name="custom_tour_name" id="custom_tour_name" 
                               class="form-control form-control-lg" 
                               placeholder="VD: Tour Sapa 3N2Đ - Đoàn riêng gia đình Nguyễn Văn A"
                               value="<?= htmlspecialchars($old['custom_tour_name'] ?? '') ?>">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Nhập tên tour tự do cho các yêu cầu đặc biệt không có trong danh sách
                        </small>
                    </div>
                </div>

                <!-- Ngày đi/về -->
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày khởi hành <span class="text-danger">*</span></label>
                        <input type="date" name="depart_date" id="depart_date" class="form-control" 
                               value="<?= htmlspecialchars($old['depart_date'] ?? '') ?>"
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày về</label>
                        <input type="date" name="return_date" id="return_date" class="form-control" 
                               value="<?= htmlspecialchars($old['return_date'] ?? '') ?>"
                               min="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- BƯỚC 2: GIÁ TOUR -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">💰 Bước 2: Giá Tour</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá người lớn (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="price_adult" id="price_adult" class="form-control" 
                               value="<?= htmlspecialchars($old['price_adult'] ?? '') ?>"
                               min="0" step="1000" placeholder="VD: 5000000" required
                               oninput="updateTotals()">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá trẻ em (VNĐ)</label>
                        <input type="number" name="price_children" id="price_children" class="form-control" 
                               value="<?= htmlspecialchars($old['price_children'] ?? '0') ?>"
                               min="0" step="1000" placeholder="VD: 3000000"
                               oninput="updateTotals()">
                    </div>
                </div>
            </div>
        </div>

        <!-- BƯỚC 3: THÔNG TIN KHÁCH -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-warning">
                <h5 class="mb-0">👤 Bước 3: Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" name="contact_name" class="form-control" 
                               value="<?= htmlspecialchars($old['contact_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="contact_phone" class="form-control" 
                               value="<?= htmlspecialchars($old['contact_phone'] ?? '') ?>"
                               placeholder="0912345678" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="contact_email" class="form-control" 
                               value="<?= htmlspecialchars($old['contact_email'] ?? '') ?>"
                               placeholder="example@email.com">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Người lớn <span class="text-danger">*</span></label>
                        <input type="number" name="adults" id="adults" class="form-control" 
                               value="<?= htmlspecialchars($old['adults'] ?? '1') ?>" 
                               min="0" required oninput="updateTotals()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Trẻ em</label>
                        <input type="number" name="children" id="children" class="form-control" 
                               value="<?= htmlspecialchars($old['children'] ?? '0') ?>" 
                               min="0" oninput="updateTotals()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tổng người</label>
                        <input type="number" id="total_people" class="form-control" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- BƯỚC 4: YÊU CẦU ĐẶC BIỆT -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">📝 Bước 4: Yêu cầu đặc biệt</h5>
            </div>
            <div class="card-body">
                <textarea name="special_request" class="form-control" rows="3" 
                          placeholder="VD: Cần phòng đơn, ăn chay, xe riêng..."><?= htmlspecialchars($old['special_request'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- BƯỚC 5: DỊCH VỤ BỔ SUNG -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">🛎️ Bước 5: Dịch vụ bổ sung (tùy chọn)</h5>
            </div>
            <div class="card-body">
                <div id="items-container"></div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItemRow()">
                    + Thêm dịch vụ
                </button>
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
                        <p class="mb-2"><strong>Tiền tour:</strong> <span id="tour_amount">0</span> đ</p>
                        <p class="mb-2"><strong>Dịch vụ bổ sung:</strong> <span id="items_amount">0</span> đ</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h3 class="mb-0 fw-bold text-danger">
                            TỔNG: <span id="total_amount">0</span> đ
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-check-circle me-2"></i>Tạo Booking
            </button>
            <a href="index.php?act=admin-booking" class="btn btn-secondary btn-lg">
                <i class="bi bi-x-circle me-2"></i>Hủy
            </a>
        </div>
    </form>
</div>

<script>
let itemIndex = 0;

// Switch giữa 2 mode
function switchMode(mode) {
    const existingSection = document.getElementById('existingTourSection');
    const customSection = document.getElementById('customTourSection');
    const tourSelect = document.getElementById('tour_schedule'); // sửa id
    const customInput = document.getElementById('custom_tour_name');
    
    if (mode === 'existing') {
        existingSection.style.display = 'block';
        customSection.style.display = 'none';
        tourSelect.required = true;
        customInput.required = false;
        customInput.value = '';
    } else {
        existingSection.style.display = 'none';
        customSection.style.display = 'block';
        tourSelect.required = false;
        customInput.required = true;
        tourSelect.value = '';
    }
}

// Auto-fill giá khi chọn tour có sẵn
document.getElementById('tour_schedule').addEventListener('change', function() {
    const selected = this.selectedOptions[0];
    if (selected && selected.value) {
        const duration = parseInt(selected.dataset.duration || 0);
        const adultPrice = parseFloat(selected.dataset.priceAdult || 0);
        const childPrice = parseFloat(selected.dataset.priceChildren || 0);
        
        document.getElementById('price_adult').value = adultPrice;
        document.getElementById('price_children').value = childPrice;

        const departDate = document.getElementById('depart_date').value;
        if (departDate && duration > 0) {
            const returnDate = new Date(departDate);
            returnDate.setDate(returnDate.getDate() + duration);
            document.getElementById('return_date').value = returnDate.toISOString().split('T')[0];
        }

        updateTotals();
    }
});


// Auto calculate return date
document.getElementById('depart_date').addEventListener('change', function() {
    const tourSelect = document.getElementById('tour_schedule');
    const selected = tourSelect.selectedOptions[0];
    if (selected && selected.value) {
        const duration = parseInt(selected.dataset.duration || 0);
        if (this.value && duration > 0) {
            const returnDate = new Date(this.value);
            returnDate.setDate(returnDate.getDate() + duration);
            document.getElementById('return_date').value = returnDate.toISOString().split('T')[0];
        }
    }
});

function updateTotals() {
    const adults = parseInt(document.getElementById('adults').value || 0);
    const children = parseInt(document.getElementById('children').value || 0);
    const priceAdult = parseFloat(document.getElementById('price_adult').value || 0);
    const priceChild = parseFloat(document.getElementById('price_children').value || 0);
    
    document.getElementById('total_people').value = adults + children;
    
    const tourAmount = (adults * priceAdult) + (children * priceChild);

    let itemsAmount = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty')?.value || 0);
        const price = parseFloat(row.querySelector('.item-price')?.value || 0);
        itemsAmount += qty * price;
    });

    document.getElementById('tour_amount').textContent = tourAmount.toLocaleString('vi-VN');
    document.getElementById('items_amount').textContent = itemsAmount.toLocaleString('vi-VN');
    document.getElementById('total_amount').textContent = (tourAmount + itemsAmount).toLocaleString('vi-VN');
}

function addItemRow() {
    const container = document.getElementById('items-container');
    const row = document.createElement('div');
    row.className = 'item-row row mb-2';
    row.innerHTML = `
        <div class="col-md-4">
            <input type="text" name="items[${itemIndex}][description]" class="form-control" placeholder="Tên dịch vụ">
        </div>
        <div class="col-md-2">
            <select name="items[${itemIndex}][type]" class="form-select">
                <option value="SERVICE">Dịch vụ</option>
                <option value="MEAL">Bữa ăn</option>
                <option value="ROOM">Phòng đơn</option>
                <option value="INSURANCE">Bảo hiểm</option>
                <option value="TRANSPORT">Vận chuyển</option>
                <option value="OTHER">Khác</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="items[${itemIndex}][qty]" class="form-control item-qty" placeholder="SL" min="1" value="1" oninput="updateTotals()">
        </div>
        <div class="col-md-3">
            <input type="number" name="items[${itemIndex}][unit_price]" class="form-control item-price" placeholder="Đơn giá" min="0" oninput="updateTotals()">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.item-row').remove(); updateTotals();">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
    itemIndex++;
}

// Event listeners
document.getElementById('adults').addEventListener('input', updateTotals);
document.getElementById('children').addEventListener('input', updateTotals);
document.getElementById('price_adult').addEventListener('input', updateTotals);
document.getElementById('price_children').addEventListener('input', updateTotals);

// Init
updateTotals();
</script>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

