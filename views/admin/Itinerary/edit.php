<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>✏️ Sửa lịch trình ngày <?= $itinerary['day_number'] ?></h2>
        <a href="index.php?act=admin-itinerary&tour_id=<?= $tour['id'] ?>" class="btn btn-secondary">
            ← Quay lại
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="index.php?act=admin-itinerary-update">
                <input type="hidden" name="id" value="<?= $itinerary['id'] ?>">
                <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                <input type="hidden" name="day_number" value="<?= $itinerary['day_number'] ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề ngày <?= $itinerary['day_number'] ?> <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" 
                           value="<?= htmlspecialchars($itinerary['title']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả tổng quan</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($itinerary['description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Chi tiết hoạt động</label>
                    <textarea name="activities" class="form-control" rows="8"><?= htmlspecialchars($itinerary['activities']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">🏨 Chỗ nghỉ</label>
                        <input type="text" name="accommodation" class="form-control" 
                               value="<?= htmlspecialchars($itinerary['accommodation']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">🍽️ Bữa ăn</label>
                        <input type="text" name="meals" class="form-control" 
                               value="<?= htmlspecialchars($itinerary['meals']) ?>">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Cập nhật
                    </button>
                    <a href="index.php?act=admin-itinerary&tour_id=<?= $tour['id'] ?>" 
                       class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">