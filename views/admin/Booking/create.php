<?php
// Lấy old data nếu có lỗi
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
                <div class="mb-3">
                    <label class="form-label fw-bold">Tour <span class="text-danger">*</span></label>
                    <select name="tour_id" id="tour_id" class="form-select" required>
                        <option value="">-- Chọn tour --</option>
                        <?php foreach ($tours as $t): ?>
                            <option value="<?= $t['id'] ?>"
                                data-duration="<?= $t['duration_days'] ?>"
                                <?= ($old['tour_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                                [<?= htmlspecialchars($t['code']) ?>] <?= htmlspecialchars($t['title']) ?>
                                (<?= $t['duration_days'] ?> ngày - <?= htmlspecialchars($t['category_name'] ?? 'N/A') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày khởi hành <span class="text-danger">*</span></label>
                        <input type="date" name="depart_date" id="depart_date" class="form-control" 
                               value="<?= htmlspecialchars($old['depart_date'] ?? '') ?>"
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày về <small class="text-muted">(tùy chọn)</small></label>
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
                        <label class="form-label fw-bold">Giá trẻ em (VNĐ) <small class="text-muted">(dưới 10 tuổi)</small></label>
                        <input type="number" name="price_children" id="price_children" class="form-control" 
                               value="<?= htmlspecialchars($old['price_children'] ?? '0') ?>"
                               min="0" step="1000" placeholder="VD: 3000000"
                               oninput="updateTotals()">
                    </div>
                </div>
            </div>
        </div>

        <!-- BƯỚC 3: THÔNG TIN KHÁCH HÀNG -->
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
                        <label class="form-label fw-bold">Trẻ em (dưới 10 tuổi)</label>
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
                          placeholder="VD: Cần phòng đơn, ăn chay, xe riêng, hướng dẫn viên tiếng Anh..."><?= htmlspecialchars($old['special_request'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- BƯỚC 5: DỊCH VỤ BỔ SUNG (TÙY CHỌN) -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">🛎️ Bước 5: Dịch vụ bổ sung (tùy chọn)</h5>
            </div>
            <div class="card-body">
                <div id="items-container">
                    <!-- Items sẽ được thêm vào đây -->
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItemRow()">
                    + Thêm dịch vụ
                </button>
                <small class="text-muted d-block mt-2">
                    <i class="bi bi-info-circle"></i> 
                    Dịch vụ bổ sung: Phòng đơn, Bảo hiểm, Bữa ăn đặc biệt, Thuê xe riêng...
                </small>
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

// Auto calculate return date
document.getElementById('tour_id').addEventListener('change', function() {
    const selected = this.selectedOptions[0];
    const duration = parseInt(selected.dataset.duration || 0);
    const departDate = document.getElementById('depart_date').value;
    
    if (departDate && duration > 0) {
        const returnDate = new Date(departDate);
        returnDate.setDate(returnDate.getDate() + duration);
        document.getElementById('return_date').value = returnDate.toISOString().split('T')[0];
    }
});

// Auto calculate return date when depart_date changes
document.getElementById('depart_date').addEventListener('change', function() {
    const tourSelect = document.getElementById('tour_id');
    const selected = tourSelect.selectedOptions[0];
    const duration = parseInt(selected.dataset.duration || 0);
    
    if (this.value && duration > 0) {
        const returnDate = new Date(this.value);
        returnDate.setDate(returnDate.getDate() + duration);
        document.getElementById('return_date').value = returnDate.toISOString().split('T')[0];
    }
});

// Cập nhật tổng tiền
function updateTotals() {
    const adults = parseInt(document.getElementById('adults').value || 0);
    const children = parseInt(document.getElementById('children').value || 0);
    const priceAdult = parseFloat(document.getElementById('price_adult').value || 0);
    const priceChild = parseFloat(document.getElementById('price_children').value || 0);
    
    document.getElementById('total_people').value = adults + children;
    
    const tourAmount = (adults * priceAdult) + (children * priceChild);

    // Tính tổng dịch vụ bổ sung
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

// Thêm dịch vụ bổ sung
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