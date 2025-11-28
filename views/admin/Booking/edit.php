<div class="container mt-4">

    <h3 class="mb-3">Sửa Booking</h3>

    <form action="index.php?act=admin-booking-update" method="POST">

        <input type="hidden" name="id" value="<?= $booking['id'] ?>">

        <div class="card p-4 shadow-sm">

            <div class="form-group">
                <label>Mã booking</label>
                <input type="text" name="booking_code" class="form-control"
                       value="<?= $booking['booking_code'] ?>" required>
            </div>

            <div class="form-group">
                <label>Chọn Tour</label>
                <select name="tour_schedule_id" class="form-control" required>
                    <?php foreach ($schedules as $sc): ?>
                        <option value="<?= $sc['id'] ?>"
                            <?= ($booking['tour_schedule_id'] == $sc['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sc['tour_title'] ?? 'Unknown') ?> - <?= $sc['depart_date'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Họ tên khách</label>
                    <input type="text" name="contact_name" class="form-control"
                           value="<?= $booking['contact_name'] ?>" required>
                </div>

                <div class="col-md-4 form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="contact_phone" class="form-control"
                           value="<?= $booking['contact_phone'] ?>">
                </div>

                <div class="col-md-4 form-group">
                    <label>Email</label>
                    <input type="email" name="contact_email" class="form-control"
                           value="<?= $booking['contact_email'] ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Người lớn</label>
                    <input type="number" name="adults" class="form-control" id="adults"
                           value="<?= $booking['adults'] ?>">
                </div>

                <div class="col-md-4 form-group">
                    <label>Trẻ em</label>
                    <input type="number" name="children" class="form-control" id="children"
                           value="<?= $booking['children'] ?>">
                </div>

                <div class="col-md-4 form-group">
                    <label>Tổng người</label>
                    <input type="number" name="total_people" class="form-control"
                           id="total_people" readonly
                           value="<?= $booking['total_people'] ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Tổng tiền</label>
                <input type="number" name="total_amount" class="form-control"
                       value="<?= $booking['total_amount'] ?>">
            </div>

            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status" class="form-control">
                    <?php foreach (['PENDING','CONFIRMED','PAID','COMPLETED','CANCELED'] as $st): ?>
                        <option value="<?= $st ?>" 
                            <?= ($booking['status'] == $st ? 'selected' : '') ?>>
                            <?= $st ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="btn btn-primary">Cập nhật</button>
            <a href="index.php?act=admin-booking" class="btn btn-secondary">Quay lại</a>

        </div>
    </form>
</div>

<script>
function updateTotal() {
    let a = parseInt(document.getElementById("adults").value) || 0;
    let c = parseInt(document.getElementById("children").value) || 0;
    document.getElementById("total_people").value = a + c;
}

document.getElementById("adults").oninput = updateTotal;
document.getElementById("children").oninput = updateTotal;
</script>
