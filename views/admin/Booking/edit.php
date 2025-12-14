<!-- views/admin/Booking/edit.php - CHẾ ĐỘ SỬA THÔNG MINH -->
<?php
$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);

// ✅ XÁC ĐỊNH CẤP ĐỘ SỬA
$isCustom = isset($booking['is_custom_request']) && (int)$booking['is_custom_request'] === 1;
$isFinished = in_array($booking['status'], ['COMPLETED', 'CANCELED', 'REFUNDED']);
$isPastDate = !empty($booking['depart_date']) && strtotime($booking['depart_date']) < strtotime('today');
$paymentStatus = $booking['payment_status'] ?? 'PENDING';
$isFullPaid = ($paymentStatus === 'FULL_PAID');
$isInProgress = ($booking['status'] === 'IN_PROGRESS');

// 🎯 3 CẤP ĐỘ SỬA
if ($isCustom || $isFinished || $isPastDate) {
    $editMode = 'FULL'; // Sửa tất cả
    $editLabel = '✅ Chế độ sửa đầy đủ';
    $editColor = 'success';
    $editReason = $isCustom ? 'Tour theo yêu cầu' : ($isFinished ? 'Booking đã kết thúc' : 'Tour đã qua ngày khởi hành');
} elseif ($isFullPaid && $isInProgress) {
    $editMode = 'VIEW_ONLY'; // Chỉ xem
    $editLabel = '🔒 Chế độ chỉ xem';
    $editColor = 'secondary';
    $editReason = 'Tour đang diễn ra và đã thanh toán đủ';
} else {
    $editMode = 'LIMITED'; // Sửa giới hạn
    $editLabel = '⚠️ Chế độ sửa có giới hạn';
    $editColor = 'warning';
    $editReason = 'Tour thường đang hoạt động';
}
?>

<div class="container mt-4">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">✏️ Sửa Booking #<?= htmlspecialchars($booking['booking_code']) ?></h2>
        <div>
            <a href="index.php?act=admin-booking-detail&id=<?= $booking['id'] ?>" class="btn btn-info">
                <i class="bi bi-eye"></i> Chi tiết
            </a>
            <a href="index.php?act=admin-booking" class="btn btn-secondary">← Quay lại</a>
        </div>
    </div>

    <!-- ALERTS -->
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

    <!-- ✅ THÔNG BÁO CHẾ ĐỘ SỬA -->
    <div class="alert alert-<?= $editColor ?> shadow-sm">
        <div class="d-flex align-items-start">
            <i class="bi bi-<?= $editMode === 'FULL' ? 'check-circle-fill' : ($editMode === 'VIEW_ONLY' ? 'lock-fill' : 'exclamation-triangle-fill') ?> fs-3 me-3"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-2"><?= $editLabel ?></h5>
                <p class="mb-2"><strong>Lý do:</strong> <?= $editReason ?></p>
                
                <?php if ($editMode === 'FULL'): ?>
                    <ul class="mb-0">
                        <li>✅ Có thể sửa <strong>TẤT CẢ</strong> thông tin</li>
                        <li>✅ Đổi tour, giá, số người, ngày đi</li>
                    </ul>
                
                <?php elseif ($editMode === 'LIMITED'): ?>
                    <ul class="mb-2">
                        <li>✅ <strong>Có thể sửa:</strong> Tour, Giá, Số người (nếu còn chỗ), Thông tin khách</li>
                        <li>⚠️ <strong>Lưu ý:</strong> Hệ thống sẽ kiểm tra tính khả thi trước khi lưu</li>
                    </ul>
                    <div class="alert alert-info mb-0 mt-2">
                        <i class="bi bi-lightbulb-fill"></i>
                        <strong>Mẹo:</strong> Nếu cần thay đổi lớn → Hủy booking này và tạo booking mới
                    </div>
                
                <?php else: // VIEW_ONLY ?>
                    <ul class="mb-2">
                        <li>✅ <strong>Có thể sửa:</strong> Thông tin khách, Ghi chú</li>
                        <li>❌ <strong>Không thể sửa:</strong> Tour, Giá, Số người (Tour đang diễn ra + Đã thanh toán đủ)</li>
                    </ul>
                    <div class="alert alert-danger mb-0 mt-2">
                        <i class="bi bi-shield-fill-exclamation"></i>
                        <strong>Bảo vệ dữ liệu:</strong> Booking này đã khóa để tránh thay đổi trong khi tour đang chạy
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <form action="index.php?act=admin-booking-update" method="POST" id="bookingForm">
        <input type="hidden" name="id" value="<?= $booking['id'] ?>">
        <input type="hidden" name="edit_mode" value="<?= $editMode ?>">

        <!-- =============================================
             🎯 CHỌN TOUR
             ============================================= -->
        <?php if ($editMode !== 'VIEW_ONLY'): ?>
            <div class="card mb-3 shadow-sm border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-check"></i> Chọn Tour
                        <?php if ($editMode === 'LIMITED'): ?>
                            <span class="badge bg-warning text-dark float-end">Có thể đổi tour</span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($isCustom): ?>
                        <!-- Tour custom: Hiển thị info -->
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-star-fill"></i>
                            <strong>Tour theo yêu cầu:</strong> <?= htmlspecialchars($booking['tour_name']) ?>
                        </div>
                        <input type="hidden" name="tour_schedule_id" value="<?= $booking['tour_schedule_id'] ?>">
                    <?php else: ?>
                        <!-- Tour thường: Cho chọn lại -->
                        <label class="form-label fw-bold">
                            Lịch tour <span class="text-danger">*</span>
                        </label>
                        <select name="tour_schedule_id" id="tour_schedule_select" 
                                class="form-select form-select-lg" required>
                            <?php foreach ($schedules as $sc): ?>
                                <?php
                                $tourTitle = htmlspecialchars($sc['tour_title'] ?? '');
                                $category = htmlspecialchars($sc['category_name'] ?? '');
                                $departDate = date('d/m/Y', strtotime($sc['depart_date']));
                                $duration = (int)($sc['duration_days'] ?? 0);
                                $priceAdult = (float)($sc['price_adult'] ?? 0);
                                $priceChildren = (float)($sc['price_children'] ?? 0);
                                $seatsAvail = (int)($sc['seats_available'] ?? 0);
                                $seatsTotal = (int)($sc['seats_total'] ?? 0);
                                $disabled = ($seatsAvail <= 0) ? 'disabled' : '';
                                $isSelected = ($booking['tour_schedule_id'] == $sc['id']);
                                ?>
                                <option value="<?= $sc['id'] ?>" 
                                    data-price-adult="<?= $priceAdult ?>"
                                    data-price-children="<?= $priceChildren ?>"
                                    data-seats-available="<?= $seatsAvail ?>"
                                    data-seats-total="<?= $seatsTotal ?>"
                                    <?= $isSelected ? 'selected' : '' ?>
                                    <?= $disabled ?>>
                                    [<?= $category ?>] <?= $tourTitle ?> - <?= $departDate ?>
                                    (Còn <?= $seatsAvail ?>/<?= $seatsTotal ?> chỗ)
                                    - <?= number_format($priceAdult) ?>đ/NL
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted mt-2 d-block">
                            <i class="bi bi-info-circle"></i>
                            Giá và số chỗ sẽ tự động cập nhật khi chọn tour
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- VIEW_ONLY: Chỉ hiển thị -->
            <input type="hidden" name="tour_schedule_id" value="<?= $booking['tour_schedule_id'] ?>">
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-lock-fill"></i> Thông tin Tour (Đã khóa)</h5>
                </div>
                <div class="card-body">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-calendar-check text-primary"></i>
                        </span>
                        <input type="text" class="form-control bg-light fw-bold" 
                            value="<?= htmlspecialchars($booking['tour_name']) ?>" readonly>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- =============================================
             📅 NGÀY ĐI/VỀ
             ============================================= -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-range"></i> Lịch trình</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày khởi hành</label>
                        <input type="date" name="depart_date" 
                            class="form-control form-control-lg <?= $editMode === 'VIEW_ONLY' ? 'bg-light' : '' ?>"
                            value="<?= htmlspecialchars($booking['depart_date'] ?? '') ?>" 
                            <?= $editMode === 'VIEW_ONLY' ? 'readonly' : 'min="' . date('Y-m-d') . '"' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày về</label>
                        <?php
                        $returnDate = $booking['return_date'] ?? '';
                        if (empty($returnDate) && !empty($booking['depart_date']) && !empty($booking['duration_days'])) {
                            $returnTimestamp = strtotime("+{$booking['duration_days']} days", strtotime($booking['depart_date']));
                            $returnDate = date('Y-m-d', $returnTimestamp);
                        }
                        ?>
                        <input type="date" name="return_date" 
                            class="form-control form-control-lg <?= $editMode === 'VIEW_ONLY' ? 'bg-light' : '' ?>"
                            value="<?= htmlspecialchars($returnDate) ?>" 
                            <?= $editMode === 'VIEW_ONLY' ? 'readonly' : 'min="' . date('Y-m-d') . '"' ?>>
                    </div>
                </div>
            </div>
        </div>

        <!-- =============================================
             💰 GIÁ TOUR
             ============================================= -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-cash-stack"></i> Giá Tour
                    <?php if ($editMode === 'LIMITED'): ?>
                        <span class="badge bg-warning text-dark float-end">Có thể điều chỉnh</span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá người lớn (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="price_adult" id="price_adult" 
                            class="form-control form-control-lg <?= $editMode === 'VIEW_ONLY' ? 'bg-light' : '' ?>"
                            value="<?= htmlspecialchars($booking['price_adult'] ?? '0') ?>" 
                            min="0" step="1000" required
                            <?= $editMode === 'VIEW_ONLY' ? 'readonly' : 'oninput="updateTotals()"' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá trẻ em (VNĐ)</label>
                        <input type="number" name="price_children" id="price_children" 
                            class="form-control form-control-lg <?= $editMode === 'VIEW_ONLY' ? 'bg-light' : '' ?>"
                            value="<?= htmlspecialchars($booking['price_children'] ?? '0') ?>" 
                            min="0" step="1000"
                            <?= $editMode === 'VIEW_ONLY' ? 'readonly' : 'oninput="updateTotals()"' ?>>
                    </div>
                </div>
                <?php if ($editMode !== 'VIEW_ONLY'): ?>
                    <small class="text-muted mt-2 d-block">
                        <i class="bi bi-info-circle"></i> Tự động điền khi chọn tour, có thể chỉnh sửa
                    </small>
                <?php endif; ?>
            </div>
        </div>

        <!-- =============================================
             👤 THÔNG TIN KHÁCH HÀNG
             ============================================= -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" name="contact_name" class="form-control form-control-lg"
                            value="<?= htmlspecialchars($booking['contact_name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Điện thoại</label>
                        <input type="text" name="contact_phone" class="form-control form-control-lg"
                            value="<?= htmlspecialchars($booking['contact_phone']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="contact_email" class="form-control form-control-lg"
                            value="<?= htmlspecialchars($booking['contact_email'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            Người lớn
                            <?php if ($editMode === 'LIMITED'): ?>
                                <i class="bi bi-exclamation-triangle-fill text-warning" 
                                   title="Thay đổi sẽ check chỗ trống"></i>
                            <?php endif; ?>
                        </label>
                        <input type="number" name="adults" id="adults" 
                            class="form-control form-control-lg text-center fw-bold <?= $editMode === 'VIEW_ONLY' ? 'bg-light' : '' ?>"
                            value="<?= $booking['adults'] ?>" min="0" required
                            <?= $editMode === 'VIEW_ONLY' ? 'readonly' : 'oninput="updateTotals()"' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Trẻ em</label>
                        <input type="number" name="children" id="children" 
                            class="form-control form-control-lg text-center fw-bold <?= $editMode === 'VIEW_ONLY' ? 'bg-light' : '' ?>"
                            value="<?= $booking['children'] ?>" min="0"
                            <?= $editMode === 'VIEW_ONLY' ? 'readonly' : 'oninput="updateTotals()"' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tổng người</label>
                        <input type="number" id="total_people" 
                            class="form-control form-control-lg text-center fw-bold bg-light text-primary"
                            value="<?= $booking['total_people'] ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- =============================================
             🚦 TRẠNG THÁI
             ============================================= -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-flag-fill"></i> Trạng thái</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Trạng thái Booking</label>
                        <select name="status" class="form-select form-select-lg" id="booking_status">
                            <?php foreach (BookingModel::$statusLabels as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $booking['status'] === $key ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Trạng thái Thanh toán</label>
                        <div class="form-control form-select-lg bg-light">
                            <?php
                            $badge = match ($paymentStatus) {
                                'FULL_PAID' => '<span class="badge bg-success fs-5">💰 Đã thanh toán đủ</span>',
                                'DEPOSIT_PAID' => '<span class="badge bg-info fs-5">💵 Đã cọc</span>',
                                default => '<span class="badge bg-secondary fs-5">⏸️ Chưa thanh toán</span>'
                            };
                            echo $badge;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =============================================
             📝 YÊU CẦU ĐẶC BIỆT
             ============================================= -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-chat-left-text-fill"></i> Yêu cầu đặc biệt</h5>
            </div>
            <div class="card-body">
                <textarea name="special_request" class="form-control form-control-lg" rows="3" 
                    placeholder="Ghi chú đặc biệt..."><?= htmlspecialchars($booking['special_request'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- =============================================
             🛎️ DỊCH VỤ BỔ SUNG
             ============================================= -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-bag-plus-fill"></i> Dịch vụ bổ sung</h5>
            </div>
            <div class="card-body">
                <div id="items-container">
                    <?php foreach ($items as $idx => $item): ?>
                        <div class="item-row card mb-3 border-start border-4 border-info">
                            <div class="card-body">
                                <input type="hidden" name="items[<?= $idx ?>][id]" value="<?= $item['id'] ?>">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Tên dịch vụ</label>
                                        <input type="text" name="items[<?= $idx ?>][description]" class="form-control"
                                            value="<?= htmlspecialchars($item['description']) ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold small">Loại</label>
                                        <select name="items[<?= $idx ?>][type]" class="form-select">
                                            <?php
                                            $types = ['SERVICE' => '🔧 Dịch vụ', 'MEAL' => '🍽️ Bữa ăn', 'ROOM' => '🏨 Phòng đơn', 
                                                      'INSURANCE' => '🛡️ Bảo hiểm', 'TRANSPORT' => '🚗 Vận chuyển', 'OTHER' => '📦 Khác'];
                                            foreach ($types as $k => $v):
                                            ?>
                                                <option value="<?= $k ?>" <?= $item['type'] == $k ? 'selected' : '' ?>><?= $v ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold small">Số lượng</label>
                                        <input type="number" name="items[<?= $idx ?>][qty]" class="form-control item-qty text-center"
                                            value="<?= $item['qty'] ?>" min="1" oninput="updateTotals()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">Đơn giá (VNĐ)</label>
                                        <input type="number" name="items[<?= $idx ?>][unit_price]" class="form-control item-price text-end"
                                            value="<?= $item['unit_price'] ?>" min="0" step="1000" oninput="updateTotals()">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger w-100"
                                            onclick="this.closest('.item-row').remove(); updateTotals();">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-outline-primary" onclick="addItemRow()">
                    <i class="bi bi-plus-circle"></i> Thêm dịch vụ
                </button>
            </div>
        </div>

        <!-- =============================================
             💵 TỔNG TIỀN
             ============================================= -->
        <div class="card mb-3 shadow border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-calculator-fill"></i> Tổng thanh toán</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Tiền tour:</span>
                            <span class="fs-5 text-primary"><span id="tour_amount">0</span> đ</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Dịch vụ bổ sung:</span>
                            <span class="fs-5 text-info"><span id="items_amount">0</span> đ</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="text-muted mb-1">TỔNG CỘNG</p>
                        <h2 class="mb-0 fw-bold text-danger">
                            <span id="total_amount">0</span> đ
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-check-circle me-2"></i>Lưu thay đổi
            </button>
            <a href="index.php?act=admin-booking-detail&id=<?= $booking['id'] ?>" class="btn btn-info btn-lg">
                <i class="bi bi-eye me-2"></i>Xem chi tiết
            </a>
            <a href="index.php?act=admin-booking" class="btn btn-secondary btn-lg">
                <i class="bi bi-x-circle me-2"></i>Hủy
            </a>
        </div>
    </form>
</div>

<script>
let itemIndex = <?= count($items) ?>;
const canEditFull = <?= $canEditFull ? 'true' : 'false' ?>;

function switchMode(mode) {
    const existingSection = document.getElementById('existingTourSection');
    const customSection = document.getElementById('customTourSection');
    const scheduleSelect = document.getElementById('tour_schedule_select');

    if (mode === 'existing') {
        existingSection.style.display = 'block';
        customSection.style.display = 'none';
        scheduleSelect.required = true;
    } else {
        existingSection.style.display = 'none';
        customSection.style.display = 'block';
        scheduleSelect.required = false;
        scheduleSelect.value = '';
    }
}

// Auto-fill giá khi chọn lịch tour
// ===== TIẾP TỤC TỪ DÒNG BỊ CẮT =====

// Auto-fill giá khi chọn lịch tour
document.getElementById('tour_schedule_select')?.addEventListener('change', function() {
    const selected = this.selectedOptions[0];
    if (selected && selected.value) {
        const priceAdult = parseFloat(selected.dataset.priceAdult || 0);
        const priceChildren = parseFloat(selected.dataset.priceChildren || 0);
        
        document.getElementById('price_adult').value = priceAdult;
        document.getElementById('price_children').value = priceChildren;
        
        updateTotals();
    }
});

// Cập nhật tổng tiền
function updateTotals() {
    const adults = parseInt(document.getElementById('adults').value || 0);
    const children = parseInt(document.getElementById('children').value || 0);
    
    // Tổng người
    document.getElementById('total_people').value = adults + children;

    // Giá tour
    const priceAdult = parseFloat(document.getElementById('price_adult').value || 0);
    const priceChild = parseFloat(document.getElementById('price_children').value || 0);
    const tourAmount = (adults * priceAdult) + (children * priceChild);

    // Tổng dịch vụ bổ sung
    let itemsAmount = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty')?.value || 0);
        const price = parseFloat(row.querySelector('.item-price')?.value || 0);
        itemsAmount += qty * price;
    });

    // Hiển thị
    document.getElementById('tour_amount').textContent = tourAmount.toLocaleString('vi-VN');
    document.getElementById('items_amount').textContent = itemsAmount.toLocaleString('vi-VN');
    document.getElementById('total_amount').textContent = (tourAmount + itemsAmount).toLocaleString('vi-VN');
}

// Thêm dịch vụ mới
function addItemRow() {
    const container = document.getElementById('items-container');
    const row = document.createElement('div');
    row.className = 'item-row card mb-3 border-start border-4 border-success';
    row.innerHTML = `
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Tên dịch vụ</label>
                    <input type="text" name="items[${itemIndex}][description]" class="form-control" 
                        placeholder="Mô tả dịch vụ...">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Loại</label>
                    <select name="items[${itemIndex}][type]" class="form-select">
                        <option value="SERVICE">🔧 Dịch vụ</option>
                        <option value="MEAL">🍽️ Bữa ăn</option>
                        <option value="ROOM">🏨 Phòng đơn</option>
                        <option value="INSURANCE">🛡️ Bảo hiểm</option>
                        <option value="TRANSPORT">🚗 Vận chuyển</option>
                        <option value="OTHER">📦 Khác</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Số lượng</label>
                    <input type="number" name="items[${itemIndex}][qty]" class="form-control item-qty text-center" 
                        value="1" min="1" oninput="updateTotals()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Đơn giá (VNĐ)</label>
                    <input type="number" name="items[${itemIndex}][unit_price]" class="form-control item-price text-end" 
                        value="0" min="0" step="1000" oninput="updateTotals()">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger w-100"
                        onclick="this.closest('.item-row').remove(); updateTotals();" title="Xóa">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.appendChild(row);
    itemIndex++;
}

// Validate COMPLETED status
document.getElementById('booking_status')?.addEventListener('change', function() {
    const selected = this.selectedOptions[0];
    const warning = document.getElementById('completed_warning');
    const paymentStatus = '<?= $paymentStatus ?>';
    
    if (selected.value === 'COMPLETED' && paymentStatus !== 'FULL_PAID') {
        warning?.classList.remove('d-none');
        if (!confirm('⚠️ CẢNH BÁO\n\nBooking chưa thanh toán đủ!\n\nBạn có chắc muốn chuyển sang HOÀN TẤT?\n\n(Backend sẽ từ chối nếu chưa thanh toán đủ)')) {
            this.value = '<?= $booking['status'] ?>';
            warning?.classList.add('d-none');
        }
    } else {
        warning?.classList.add('d-none');
    }
});

// Form validation trước khi submit
document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
    const adults = parseInt(document.getElementById('adults').value || 0);
    const children = parseInt(document.getElementById('children').value || 0);
    
    if (adults + children <= 0) {
        e.preventDefault();
        alert('⚠️ Tổng số người phải lớn hơn 0!');
        return false;
    }
    
    // Kiểm tra nếu đang ở chế độ tour thường và chưa chọn lịch
    const mode = document.querySelector('input[name="tour_mode"]:checked')?.value;
    if (mode === 'existing') {
        const scheduleId = document.getElementById('tour_schedule_select')?.value;
        if (!scheduleId) {
            e.preventDefault();
            alert('⚠️ Vui lòng chọn lịch tour!');
            return false;
        }
    }
    
    return true;
});

// Auto dismiss alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alert) || new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Initialize totals
    updateTotals();
});

// Prevent accidental form submission on Enter
document.getElementById('bookingForm')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
        return false;
    }
});