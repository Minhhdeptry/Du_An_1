<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">✏️ Sửa Booking #<?= htmlspecialchars($booking['booking_code']) ?></h2>
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

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="index.php?act=admin-booking-update" method="POST" id="bookingForm">
        <input type="hidden" name="id" value="<?= $booking['id'] ?>">

        <!-- Thông tin Tour -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📅 Thông tin Tour</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Mã Booking</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($booking['booking_code']) ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tour <span class="text-danger">*</span></label>
                    <select name="tour_schedule_id" id="tour_schedule" class="form-select" required>
                        <?php foreach ($schedules as $sc): ?>
                            <option value="<?= $sc['id'] ?>"
                                data-price-adult="<?= $sc['price_adult'] ?>"
                                data-price-children="<?= $sc['price_children'] ?>"
                                <?= $booking['tour_schedule_id'] == $sc['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sc['tour_title']) ?> - 
                                <?= date('d/m/Y', strtotime($sc['depart_date'])) ?> - 
                                <?= number_format($sc['price_adult']) ?>đ
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <?php foreach ($statusText as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $booking['status'] == $key ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Thông tin khách hàng -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">👤 Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" name="contact_name" class="form-control" 
                               value="<?= htmlspecialchars($booking['contact_name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Điện thoại</label>
                        <input type="text" name="contact_phone" class="form-control" 
                               value="<?= htmlspecialchars($booking['contact_phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="contact_email" class="form-control" 
                               value="<?= htmlspecialchars($booking['contact_email'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Người lớn <span class="text-danger">*</span></label>
                        <input type="number" name="adults" id="adults" class="form-control" 
                               value="<?= $booking['adults'] ?>" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Trẻ em</label>
                        <input type="number" name="children" id="children" class="form-control" 
                               value="<?= $booking['children'] ?>" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tổng người</label>
                        <input type="number" id="total_people" class="form-control" 
                               value="<?= $booking['total_people'] ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yêu cầu đặc biệt -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-warning">
                <h5 class="mb-0">📝 Yêu cầu đặc biệt</h5>
            </div>
            <div class="card-body">
                <textarea name="special_request" class="form-control" rows="3"><?= htmlspecialchars($booking['special_request'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Dịch vụ bổ sung -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">🛎️ Dịch vụ bổ sung</h5>
            </div>
            <div class="card-body">
                <div id="items-container">
                    <?php foreach ($items as $idx => $item): ?>
                        <div class="item-row row mb-2">
                            <input type="hidden" name="items[<?= $idx ?>][id]" value="<?= $item['id'] ?>">
                            <div class="col-md-4">
                                <input type="text" name="items[<?= $idx ?>][description]" class="form-control" 
                                       value="<?= htmlspecialchars($item['description']) ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="items[<?= $idx ?>][type]" class="form-select">
                                    <option value="SERVICE" <?= $item['type'] == 'SERVICE' ? 'selected' : '' ?>>Dịch vụ</option>
                                    <option value="MEAL" <?= $item['type'] == 'MEAL' ? 'selected' : '' ?>>Bữa ăn</option>
                                    <option value="ROOM" <?= $item['type'] == 'ROOM' ? 'selected' : '' ?>>Phòng đơn</option>
                                    <option value="INSURANCE" <?= $item['type'] == 'INSURANCE' ? 'selected' : '' ?>>Bảo hiểm</option>
                                    <option value="TRANSPORT" <?= $item['type'] == 'TRANSPORT' ? 'selected' : '' ?>>Vận chuyển</option>
                                    <option value="OTHER" <?= $item['type'] == 'OTHER' ? 'selected' : '' ?>>Khác</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="items[<?= $idx ?>][qty]" class="form-control item-qty" 
                                       value="<?= $item['qty'] ?>" min="1" oninput="updateTotals()">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="items[<?= $idx ?>][unit_price]" class="form-control item-price" 
                                       value="<?= $item['unit_price'] ?>" min="0" oninput="updateTotals()">
                            </div>
                            <div class="col-md-1">
                                <a href="index.php?act=admin-booking-delete-item&item_id=<?= $item['id'] ?>&booking_id=<?= $booking['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Xóa item này?')">
                                    🗑️
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItemRow()">
                    + Thêm dịch vụ
                </button>
            </div>
        </div>

        <!-- Tổng tiền -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">💰 Tổng tiền</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Tiền tour:</strong> <span id="tour_amount">0</span> đ</p>
                        <p class="mb-2"><strong>Dịch vụ bổ sung:</strong> <span id="items_amount">0</span> đ</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h4 class="text-danger">
                            <strong>Tổng cộng:</strong> 
                            <span id="total_amount">0</span> đ
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lịch sử thay đổi -->
        <?php if (!empty($statusHistory)): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">📜 Lịch sử thay đổi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Người thực hiện</th>
                                    <th>Nội dung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statusHistory as $log): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($log['author_name'] ?? 'System') ?></td>
                                        <td><?= htmlspecialchars($log['content']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Buttons -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                💾 Lưu thay đổi
            </button>
            
            <?php if ($booking['status'] !== 'CANCELED'): ?>
                <a href="index.php?act=admin-booking-cancel&id=<?= $booking['id'] ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('Hủy booking này?')">
                    🗑️ Hủy booking
                </a>
            <?php endif; ?>

            <?php if ($booking['status'] === 'PENDING'): ?>
                <a href="index.php?act=admin-booking-confirm&id=<?= $booking['id'] ?>" 
                   class="btn btn-success">
                    ✅ Xác nhận booking
                </a>
            <?php endif; ?>

            <a href="index.php?act=admin-booking" class="btn btn-secondary">
                ✖ Hủy
            </a>
        </div>
    </form>
</div>

<script>
let itemIndex = <?= count($items) ?>;

function updateTotals() {
    const adults = parseInt(document.getElementById('adults').value || 0);
    const children = parseInt(document.getElementById('children').value || 0);
    document.getElementById('total_people').value = adults + children;

    const selected = document.getElementById('tour_schedule').selectedOptions[0];
    const priceAdult = parseFloat(selected.dataset.priceAdult || 0);
    const priceChild = parseFloat(selected.dataset.priceChildren || 0);
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
            <input type="text" name="items[${itemIndex}][description]" class="form-control" placeholder="Mô tả dịch vụ">
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
                🗑️
            </button>
        </div>
    `;
    container.appendChild(row);
    itemIndex++;
}

document.getElementById('adults').addEventListener('input', updateTotals);
document.getElementById('children').addEventListener('input', updateTotals);
document.getElementById('tour_schedule').addEventListener('change', updateTotals);

updateTotals();
</script>