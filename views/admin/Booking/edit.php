<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">✏️ Sửa Booking</h2>
        <a href="index.php?act=admin-booking" class="btn btn-secondary">← Quay lại</a>
    </div>

    <form action="index.php?act=admin-booking-update" method="POST">
        <input type="hidden" name="id" value="<?= (int)$booking['id'] ?>">
        <div class="card p-4 shadow-sm">
            <div class="mb-3">
                <label class="form-label">Mã booking</label>
                <input type="text" name="booking_code" class="form-control" value="<?= htmlspecialchars($booking['booking_code'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Chọn Tour (lịch khởi hành)</label>
                <select name="tour_schedule_id" id="tour_schedule" class="form-select" required>
                    <?php foreach ($schedules as $sc): ?>
                        <option value="<?= (int)$sc['id'] ?>"
                            data-price-adult="<?= (float)$sc['price_adult'] ?>"
                            data-price-children="<?= (float)$sc['price_children'] ?>"
                            data-seats="<?= (int)$sc['seats_available'] ?>"
                            <?= ((int)$booking['tour_schedule_id'] === (int)$sc['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sc['tour_title']) ?> - <?= htmlspecialchars($sc['depart_date']) ?>
                            (<?= (int)$sc['seats_available'] ?> chỗ còn)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Họ tên khách</label>
                    <input type="text" name="contact_name" class="form-control" value="<?= htmlspecialchars($booking['contact_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Điện thoại</label>
                    <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($booking['contact_phone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($booking['contact_email'] ?? '') ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Người lớn</label>
                    <input type="number" name="adults" id="adults" class="form-control" value="<?= (int)$booking['adults'] ?>" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Trẻ em</label>
                    <input type="number" name="children" id="children" class="form-control" value="<?= (int)$booking['children'] ?>" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tổng người</label>
                    <input type="number" id="total_people" class="form-control" value="<?= (int)$booking['total_people'] ?>" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Tổng tiền (VNĐ)</label>
                <input type="number" name="total_amount" id="total_amount" class="form-control" value="<?= (float)$booking['total_amount'] ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <?php
                    $statusText = [
                        'PENDING' => 'Chờ xử lý',
                        'CONFIRMED' => 'Đã xác nhận',
                        'PAID' => 'Đã thanh toán',
                        'COMPLETED' => 'Hoàn thành',
                        'CANCELED' => 'Đã hủy',
                    ];
                    foreach ($statusText as $k => $t) {
                        $sel = (($booking['status'] ?? 'PENDING') === $k) ? 'selected' : '';
                        echo "<option value=\"$k\" $sel>" . htmlspecialchars($t) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                
            </div>
        </div>
    </form>
</div>

<script>
function updateTotals() {
    const adults = parseInt(document.getElementById('adults').value || '0', 10);
    const children = parseInt(document.getElementById('children').value || '0', 10);
    document.getElementById('total_people').value = adults + children;

    const selected = document.getElementById('tour_schedule').selectedOptions[0];
    if (!selected) return;

    const priceAdult = parseFloat(selected.dataset.priceAdult || '0');
    const priceChild = parseFloat(selected.dataset.priceChildren || '0');
    document.getElementById('total_amount').value = (adults * priceAdult + children * priceChild).toFixed(0);
}

document.getElementById('adults').addEventListener('input', updateTotals);
document.getElementById('children').addEventListener('input', updateTotals);
document.getElementById('tour_schedule').addEventListener('change', updateTotals);

updateTotals();
</script>
