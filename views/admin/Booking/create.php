<?php
$statusText = [
    'PENDING'   => 'Chờ xử lý',
    'CONFIRMED' => 'Đã xác nhận',
    'PAID'      => 'Đã thanh toán',
    'COMPLETED' => 'Hoàn thành',
];
?>

<div class="container mt-4">

    <h3 class="mb-3">Tạo Booking Mới</h3>

    <form action="index.php?act=admin-booking-store" method="POST">

        <div class="card p-4 shadow-sm">

            <!-- Mã booking -->
            <div class="mb-3">
                <label class="form-label">Mã booking</label>
                <input type="text" name="booking_code" class="form-control" required placeholder="VD: BK001">
            </div>

            <!-- Chọn tour -->
            <div class="mb-3">
                <label class="form-label">Chọn Tour</label>
                <select name="tour_schedule_id" id="tour_schedule" class="form-select" required>
                    <?php 
                    $first = true; 
                    foreach ($schedules as $sc): ?>
                        <option value="<?= $sc['id'] ?>"
                            data-price-adult="<?= isset($sc['price_adult']) ? (float)$sc['price_adult'] : 0 ?>"
                            data-price-children="<?= isset($sc['price_children']) ? (float)$sc['price_children'] : 0 ?>"
                            data-seats="<?= $sc['seats_available'] ?? 0 ?>"
                            <?= $first ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sc['tour_title'] ?? 'Unknown') ?> - <?= $sc['depart_date'] ?>
                            (<?= $sc['seats_available'] ?? 0 ?> chỗ còn)
                        </option>
                        <?php $first = false; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Thông tin khách hàng -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Họ tên khách</label>
                    <input type="text" name="contact_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="contact_phone" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="contact_email" class="form-control">
                </div>
            </div>

            <!-- Số người -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Người lớn</label>
                    <input type="number" name="adults" id="adults" class="form-control" value="1" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Trẻ em</label>
                    <input type="number" name="children" id="children" class="form-control" value="0" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tổng người</label>
                    <input type="number" name="total_people" id="total_people" class="form-control" readonly>
                </div>
            </div>

            <!-- Tổng tiền -->
            <div class="mb-3">
                <label class="form-label">Tổng tiền (VNĐ)</label>
                <input type="number" name="total_amount" id="total_amount" class="form-control" readonly>
            </div>

            <!-- Trạng thái -->
            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <?php foreach ($statusText as $key => $text): ?>
                        <option value="<?= $key ?>"><?= $text ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Tạo Booking</button>
                <a href="index.php?act=admin-booking" class="btn btn-secondary">Quay lại</a>
            </div>

        </div>
    </form>
</div>

<script>
function updateTotals() {
    const adults = parseInt(document.getElementById("adults").value) || 0;
    const children = parseInt(document.getElementById("children").value) || 0;
    document.getElementById("total_people").value = adults + children;

    const tourSelect = document.getElementById("tour_schedule");
    const selected = tourSelect.selectedOptions[0];

    if (!selected || !selected.value) {
        document.getElementById("total_amount").value = 0;
        return;
    }

    const priceAdult = parseFloat(selected.dataset.priceAdult) || 0;
    const priceChildren = parseFloat(selected.dataset.priceChildren) || 0;

    document.getElementById("total_amount").value = adults * priceAdult + children * priceChildren;
}

// Sự kiện onchange/input
document.getElementById("adults").addEventListener("input", updateTotals);
document.getElementById("children").addEventListener("input", updateTotals);
document.getElementById("tour_schedule").addEventListener("change", updateTotals);

// Khởi tạo ngay khi load trang
updateTotals();
</script>
