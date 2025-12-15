<?php
$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);

$get = fn($key, $default = '') => $old[$key] ?? ($booking[$key] ?? $default);
$getNum = fn($key, $default = 0) => (float)($old[$key] ?? ($booking[$key] ?? $default));
$getInt = fn($key, $default = 0) => (int)($old[$key] ?? ($booking[$key] ?? $default));

$isCustom = !empty($booking['is_custom_request']) && (int)$booking['is_custom_request'] === 1;
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-title">✏️ Sửa Booking #<?= htmlspecialchars($booking['booking_code']) ?></h2>
        <div>
            <a href="index.php?act=admin-booking-detail&id=<?= $booking['id'] ?>" class="btn btn-info">
                <i class="bi bi-eye"></i> Chi tiết
            </a>
            <a href="index.php?act=admin-booking" class="btn btn-secondary">← Quay lại danh sách</a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="index.php?act=admin-booking-update" method="POST" id="bookingForm">
        <input type="hidden" name="id" value="<?= $booking['id'] ?>">

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Thông tin Tour</h5>
            </div>
            <div class="card-body">
                <?php if ($isCustom): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-star-fill"></i>
                        <strong>Tour theo yêu cầu:</strong> <?= htmlspecialchars($booking['tour_name']) ?>
                    </div>
                    <input type="hidden" name="tour_schedule_id" value="<?= $booking['tour_schedule_id'] ?>">
                <?php else: ?>
                    <label class="form-label fw-bold">Chọn lịch tour <span class="text-danger">*</span></label>
                    <select name="tour_schedule_id" id="tour_schedule_select" class="form-select form-select-lg" required>
                        <option value="">-- Chọn lịch tour --</option>
                        <?php foreach ($schedules as $sc): ?>
                            <?php
                            $selected = ($get('tour_schedule_id') ?? $booking['tour_schedule_id']) == $sc['id'];
                            $disabled = ($sc['seats_available'] ?? 0) <= 0 ? 'disabled' : '';
                            ?>
                            <option value="<?= $sc['id'] ?>"
                                data-price-adult="<?= $sc['price_adult'] ?? 0 ?>"
                                data-price-children="<?= $sc['price_children'] ?? 0 ?>"
                                <?= $selected ? 'selected' : '' ?>
                                <?= $disabled ?>>
                                [<?= htmlspecialchars($sc['category_name'] ?? '') ?>]
                                <?= htmlspecialchars($sc['tour_title'] ?? '') ?> -
                                <?= date('d/m/Y', strtotime($sc['depart_date'])) ?>
                                (Còn <?= $sc['seats_available'] ?? 0 ?>/<?= $sc['seats_total'] ?? 0 ?> chỗ)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-range"></i> Lịch trình</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày khởi hành</label>
                        <input type="date" name="depart_date" class="form-control form-control-lg"
                               value="<?= htmlspecialchars($get('depart_date') ?? $booking['depart_date'] ?? '') ?>"
                               <?= $isCustom ? '' : 'readonly' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày về</label>
                        <input type="date" name="return_date" class="form-control form-control-lg"
                               value="<?= htmlspecialchars($get('return_date') ?? $booking['return_date'] ?? '') ?>"
                               <?= $isCustom ? '' : 'readonly' ?>>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Giá Tour</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá người lớn (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="price_adult" id="price_adult" class="form-control form-control-lg"
                               value="<?= $getNum('price_adult', $booking['price_adult'] ?? 0) ?>" 
                               min="0" step="1000" required oninput="updateTotals()">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá trẻ em (VNĐ)</label>
                        <input type="number" name="price_children" id="price_children" class="form-control form-control-lg"
                               value="<?= $getNum('price_children', $booking['price_children'] ?? 0) ?>" 
                               min="0" step="1000" oninput="updateTotals()">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" name="contact_name" class="form-control form-control-lg"
                               value="<?= htmlspecialchars($get('contact_name', $booking['contact_name'])) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Điện thoại</label>
                        <input type="text" name="contact_phone" class="form-control form-control-lg"
                               value="<?= htmlspecialchars($get('contact_phone', $booking['contact_phone'] ?? '')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="contact_email" class="form-control form-control-lg"
                               value="<?= htmlspecialchars($get('contact_email', $booking['contact_email'] ?? '')) ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Người lớn</label>
                        <input type="number" name="adults" id="adults" class="form-control form-control-lg text-center"
                               value="<?= $getInt('adults', $booking['adults']) ?>" min="0" required oninput="updateTotals()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Trẻ em</label>
                        <input type="number" name="children" id="children" class="form-control form-control-lg text-center"
                               value="<?= $getInt('children', $booking['children']) ?>" min="0" oninput="updateTotals()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tổng người</label>
                        <input type="number" id="total_people" class="form-control form-control-lg text-center bg-light fw-bold"
                               value="<?= $getInt('adults', $booking['adults']) + $getInt('children', $booking['children']) ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-flag-fill"></i> Trạng thái</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Trạng thái Booking</label>
                        <select name="status" class="form-select form-select-lg">
                            <?php foreach (BookingModel::$statusLabels as $key => $label): ?>
                                <option value="<?= $key ?>" <?= ($get('status') ?? $booking['status']) === $key ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Trạng thái thanh toán</label>
                        <div class="form-control form-control-lg bg-light">
                            <?= $booking['payment_status'] === 'FULL_PAID' ? '💰 Đã thanh toán đủ' : 
                                ($booking['payment_status'] === 'DEPOSIT_PAID' ? '💵 Đã cọc' : '⏸️ Chưa thanh toán') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-chat-left-text-fill"></i> Yêu cầu đặc biệt</h5>
            </div>
            <div class="card-body">
                <textarea name="special_request" class="form-control form-control-lg" rows="4"
                          placeholder="Ghi chú, yêu cầu đặc biệt..."><?= htmlspecialchars($get('special_request', $booking['special_request'] ?? '')) ?></textarea>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-bag-plus-fill"></i> Dịch vụ bổ sung</h5>
                <button type="button" class="btn btn-sm btn-light" onclick="addItemRow()">
                    <i class="bi bi-plus-circle"></i> Thêm dịch vụ
                </button>
            </div>
            <div class="card-body">
                <div id="items-container">
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $idx => $item): ?>
                            <div class="item-row card mb-3 border-start border-4 border-info">
                                <div class="card-body">
                                    <input type="hidden" name="items[<?= $idx ?>][id]" value="<?= $item['id'] ?>">
                                    
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Tên dịch vụ</label>
                                            <input type="text" name="items[<?= $idx ?>][description]" class="form-control"
                                                   value="<?= htmlspecialchars($item['description']) ?>" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold">Loại</label>
                                            <select name="items[<?= $idx ?>][type]" class="form-select">
                                                <?php 
                                                $types = [
                                                    'SERVICE' => '🔧 Dịch vụ',
                                                    'MEAL' => '🍽️ Bữa ăn',
                                                    'ROOM' => '🏨 Phòng đơn',
                                                    'INSURANCE' => '🛡️ Bảo hiểm',
                                                    'TRANSPORT' => '🚗 Vận chuyển',
                                                    'VISA' => '📄 Visa',
                                                    'TICKET' => '🎫 Vé tham quan',
                                                    'GUIDE' => '👤 Hướng dẫn viên',
                                                    'OTHER' => '📦 Khác'
                                                ];
                                                foreach ($types as $k => $v): 
                                                ?>
                                                    <option value="<?= $k ?>" <?= $item['type'] == $k ? 'selected' : '' ?>>
                                                        <?= $v ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold">Số lượng</label>
                                            <input type="number" name="items[<?= $idx ?>][qty]" 
                                                   class="form-control item-qty text-center"
                                                   value="<?= $item['qty'] ?>" min="1" required oninput="updateTotals()">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">Đơn giá (VNĐ)</label>
                                            <input type="number" name="items[<?= $idx ?>][unit_price]" 
                                                   class="form-control item-price text-end"
                                                   value="<?= $item['unit_price'] ?>" min="0" step="1000" required 
                                                   oninput="updateTotals()">
                                        </div>
                                        <div class="col-md-1">
                                            <a href="index.php?act=admin-booking-delete-item&item_id=<?= $item['id'] ?>&booking_id=<?= $booking['id'] ?>" 
                                               class="btn btn-danger w-100"
                                               onclick="return confirm('❌ Xác nhận xóa dịch vụ này?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">Chưa có dịch vụ bổ sung nào. Nhấn nút "Thêm dịch vụ" để thêm.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card mb-5 shadow border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-calculator-fill"></i> Tổng thanh toán</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Tiền tour:</span>
                            <span class="fs-4 text-primary"><span id="tour_amount">0</span> đ</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Dịch vụ bổ sung:</span>
                            <span class="fs-4 text-info"><span id="items_amount">0</span> đ</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="text-muted mb-1">TỔNG CỘNG</p>
                        <h2 class="text-danger fw-bold mb-0"><span id="total_amount">0</span> đ</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-5">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-check-circle me-2"></i> Lưu thay đổi
            </button>
            <a href="index.php?act=admin-booking" class="btn btn-secondary btn-lg px-5">
                <i class="bi bi-x-circle me-2"></i> Hủy
            </a>
        </div>
    </form>
</div>

<script>
let itemIndex = <?= count($items) ?>; 
document.getElementById('tour_schedule_select')?.addEventListener('change', function() {
    const option = this.selectedOptions[0];
    if (option && option.value) {
        const adultPrice = parseFloat(option.dataset.priceAdult || 0);
        const childPrice = parseFloat(option.dataset.priceChildren || 0);

        document.getElementById('price_adult').value = adultPrice;
        document.getElementById('price_children').value = childPrice;
        updateTotals();
    }
});

function updateTotals() {
    const adults = parseInt(document.getElementById('adults')?.value || 0);
    const children = parseInt(document.getElementById('children')?.value || 0);
    const priceAdult = parseFloat(document.getElementById('price_adult')?.value || 0);
    const priceChild = parseFloat(document.getElementById('price_children')?.value || 0);

    const tourAmount = adults * priceAdult + children * priceChild;

    let itemsAmount = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty')?.value || 0);
        const price = parseFloat(row.querySelector('.item-price')?.value || 0);
        itemsAmount += qty * price;
    });

    const total = tourAmount + itemsAmount;

    document.getElementById('total_people').value = adults + children;
    document.getElementById('tour_amount').textContent = tourAmount.toLocaleString('vi-VN');
    document.getElementById('items_amount').textContent = itemsAmount.toLocaleString('vi-VN');
    document.getElementById('total_amount').textContent = total.toLocaleString('vi-VN');
}

function addItemRow() {
    const container = document.getElementById('items-container');
    
    const emptyMsg = container.querySelector('p.text-muted');
    if (emptyMsg) emptyMsg.remove();
    
    const row = document.createElement('div');
    row.className = 'item-row card mb-3 border-start border-4 border-success';
    row.innerHTML = `
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Tên dịch vụ</label>
                    <input type="text" name="items[${itemIndex}][description]" class="form-control" 
                           placeholder="Mô tả dịch vụ..." required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Loại</label>
                    <select name="items[${itemIndex}][type]" class="form-select">
                        <option value="SERVICE">🔧 Dịch vụ</option>
                        <option value="MEAL">🍽️ Bữa ăn</option>
                        <option value="ROOM">🏨 Phòng đơn</option>
                        <option value="INSURANCE">🛡️ Bảo hiểm</option>
                        <option value="TRANSPORT">🚗 Phương tiện di chuyển</option>
                        <option value="VISA">📄 Visa</option>
                        <option value="TICKET">🎫 Vé tham quan</option>
                        <option value="GUIDE">👤 Hướng dẫn viên</option>
                        <option value="OTHER">📦 Khác</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Số lượng</label>
                    <input type="number" name="items[${itemIndex}][qty]" 
                           class="form-control item-qty text-center" 
                           value="1" min="1" required oninput="updateTotals()">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Đơn giá (VNĐ)</label>
                    <input type="number" name="items[${itemIndex}][unit_price]" 
                           class="form-control item-price text-end" 
                           value="0" min="0" step="1000" required oninput="updateTotals()">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger w-100" 
                        onclick="this.closest('.item-row').remove(); updateTotals();"
                        title="Xóa dịch vụ này">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.appendChild(row);
    itemIndex++;
    
    // Focus vào input mô tả
    row.querySelector('input[type="text"]')?.focus();
}

// Khởi tạo khi load trang
document.addEventListener('DOMContentLoaded', function() {
    updateTotals();
    
    // Tự động ẩn alert sau 5 giây
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alert) || new bootstrap.Alert(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });
});
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">