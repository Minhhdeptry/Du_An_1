<?php
$statusText = [
    'PENDING'   => 'Chờ xử lý',
    'CONFIRMED' => 'Đã xác nhận',
    'PAID'      => 'Đã thanh toán',
    'COMPLETED' => 'Hoàn thành',
    'CANCELED'  => 'Đã hủy',
];
?>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">✏️ Sửa Booking</h2>
        <a href="index.php?act=admin-booking" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <form action="index.php?act=admin-booking-update" method="POST">
        <input type="hidden" name="id" value="<?= $booking['id'] ?>">

        <div class="card shadow-sm p-4">

            <!-- Mã booking -->
            <div class="mb-3">
                <label class="form-label">Mã booking</label>
                <input type="text" name="booking_code" class="form-control"
                       value="<?= htmlspecialchars($booking['booking_code']) ?>" required>
            </div>

            <!-- Chọn tour -->
            <div class="mb-3">
                <label class="form-label">Chọn Tour</label>
                <select name="tour_schedule_id" id="tour_schedule" class="form-select" required>
                    <?php foreach ($schedules as $sc): ?>
                        <option value="<?= $sc['id'] ?>"
                                data-price-adult="<?= $sc['price_adult'] ?? 0 ?>"
                                data-price-children="<?= $sc['price_children'] ?? 0 ?>"
                                data-seats="<?= $sc['seats_available'] ?? 0 ?>"
                                <?= ($booking['tour_schedule_id'] == $sc['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sc['tour_title'] ?? 'Unknown') ?> -
                            <?= $sc['depart_date'] ?> (<?= $sc['seats_available'] ?? 0 ?> chỗ còn)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Thông tin khách hàng -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Họ tên khách</label>
                    <input type="text" name="contact_name" class="form-control"
                           value="<?= htmlspecialchars($booking['contact_name']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="contact_phone" class="form-control"
                           value="<?= htmlspecialchars($booking['contact_phone']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="contact_email" class="form-control"
                           value="<?= htmlspecialchars($booking['contact_email']) ?>">
                </div>
            </div>

            <!-- Số người -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Người lớn</label>
                    <input type="number" name="adults" id="adults" class="form-control"
                           value="<?= $booking['adults'] ?>" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Trẻ em</label>
                    <input type="number" name="children" id="children" class="form-control"
                           value="<?= $booking['children'] ?>" min="0">
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
                        <option value="<?= $key ?>" <?= ($booking['status'] == $key ? 'selected' : '') ?>>
                            <?= $text ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Nút hành động -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
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
    const priceAdult = parseFloat(tourSelect.selectedOptions[0]?.dataset?.priceAdult || 0);
    const priceChildren = parseFloat(tourSelect.selectedOptions[0]?.dataset?.priceChildren || 0);

    document.getElementById("total_amount").value = adults * priceAdult + children * priceChildren;
}

// Sự kiện onchange/input
document.getElementById("adults").addEventListener("input", updateTotals);
document.getElementById("children").addEventListener("input", updateTotals);
document.getElementById("tour_schedule").addEventListener("change", updateTotals);

// Khởi tạo khi load
updateTotals();
</script>
