<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">✏️ Sửa Lịch khởi hành</h2>
        <a href="index.php?act=admin-schedule" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <form method="POST" action="index.php?act=admin-schedule-update" class="card p-4 shadow-sm">

        <input type="hidden" name="id" value="<?= $schedule['id'] ?>">

        <div class="form-group">
            <label>Tour</label>
            <select name="tour_id" class="form-control">
                <?php foreach ($tours as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $t['id'] == $schedule['tour_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['title']) ?> (<?= htmlspecialchars($t['category_name'] ?? 'Chưa có') ?>)
                    </option>

                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="col">
                <label>Ngày đi</label>
                <input type="date" name="depart_date" class="form-control" value="<?= $schedule['depart_date'] ?>">
            </div>
            <div class="col">
                <label>Ngày về</label>
                <input type="date" name="return_date" class="form-control" value="<?= $schedule['return_date'] ?>">
            </div>
        </div>

        <div class="form-row mt-3">
            <div class="col">
                <label>Tổng ghế</label>
                <input type="number" name="seats_total" class="form-control" value="<?= $schedule['seats_total'] ?>">
            </div>
            <div class="col">
                <label>Ghế còn lại</label>
                <input type="number" name="seats_available" class="form-control"
                    value="<?= $schedule['seats_available'] ?>">
            </div>
        </div>

        <div class="form-group mt-3">
            <label>Giá người lớn</label>
            <input type="number" name="price_adult" class="form-control" value="<?= $schedule['price_adult'] ?>">
        </div>
        <div class="form-group mt-3">
            <label>Giá trẻ em ( dưới 10 tuổi )</label>
            <input type="number" name="price_children" class="form-control" value="<?= $schedule['price_children'] ?>">
        </div>

        <div class="form-group">
            <label>Trạng thái</label>
            <select name="status" class="form-control">
                <?php
                $statuses = ["OPEN", "CLOSED", "CANCELED", "FINISHED"];
                foreach ($statuses as $st):
                    ?>
                    <option value="<?= $st ?>" <?= $schedule['status'] == $st ? 'selected' : '' ?>>
                        <?= $st ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Ghi chú</label>
            <textarea name="note" class="form-control"><?= $schedule['note'] ?></textarea>
        </div>

        <button class="btn btn-primary">Cập nhật</button>
        <a href="index.php?act=admin-schedule" class="btn btn-secondary">Hủy</a>
    </form>
</div>