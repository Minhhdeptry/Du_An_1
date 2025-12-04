<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">➕ Thêm Lịch khởi hành mới</h2>
        <a href="index.php?act=admin-schedule" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <form method="POST" action="index.php?act=admin-schedule-store" class="card p-4 shadow-sm">

        <div class="form-group">
            <label>Tour</label>
            <select name="tour_id" class="form-control" required>
                <option value="">-- Chọn tour --</option>
                <?php foreach ($tours as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= isset($schedule) && $t['id'] == $schedule['tour_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['title']) ?>
                        (<?= htmlspecialchars($t['category_name'] ?? 'Chưa có') ?>)
                    </option>
                <?php endforeach; ?>
            </select>

        </div>

        <div class="form-row">
            <div class="col">
                <label>Ngày đi</label>
                <input type="date" name="depart_date" class="form-control" required>
            </div>
            <div class="col">
                <label>Ngày về</label>
                <input type="date" name="return_date" class="form-control">
            </div>
        </div>

        <div class="form-row mt-3">
            <div class="col">
                <label>Tổng ghế</label>
                <input type="number" name="seats_total" class="form-control" required>
            </div>
            <div class="col">
                <label>Ghế còn lại</label>
                <input type="number" name="seats_available" class="form-control" required>
            </div>
        </div>

        <div class="form-group mt-3">
            <label>Giá người lớn</label>
            <input type="number" name="price_adult" class="form-control">
        </div>
        <div class="form-group mt-3">
            <label>Giá trẻ em (dưới 10 tuổi)</label>
            <input type="number" name="price_children" class="form-control">
        </div>

        <div class="form-group">
            <label>Trạng thái</label>
            <select name="status" class="form-control">
                <option value="OPEN">OPEN</option>
                <option value="CLOSED">CLOSED</option>
                <option value="CANCELED">CANCELED</option>
                <option value="FINISHED">FINISHED</option>
            </select>
        </div>

        <div class="form-group">
            <label>Ghi chú</label>
            <textarea name="note" class="form-control"></textarea>
        </div>

        <button class="btn btn-primary">Tạo lịch</button>
        <a href="index.php?act=admin-schedule" class="btn btn-secondary">Hủy</a>
    </form>
</div>