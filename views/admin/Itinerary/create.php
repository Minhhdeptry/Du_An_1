<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>➕ Thêm lịch trình ngày <?= $nextDay ?></h2>
        <a href="index.php?act=admin-itinerary&tour_id=<?= $tour['id'] ?>" class="btn btn-secondary">
            ← Quay lại
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="index.php?act=admin-itinerary-store">
                <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                <input type="hidden" name="day_number" value="<?= $nextDay ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề ngày <?= $nextDay ?> <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" 
                           placeholder="VD: Hà Nội - Hạ Long Bay" required>
                    <small class="text-muted">Tên gọn cho ngày này</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả tổng quan</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Mô tả ngắn gọn về ngày này..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Chi tiết hoạt động</label>
                    <textarea name="activities" class="form-control" rows="8"
                              placeholder="VD:
8:00 - Xe đón tại khách sạn
10:00 - Đến Vịnh Hạ Long, lên tàu
12:00 - Ăn trưa trên tàu
14:00 - Tham quan Hang Sửng Sốt
16:00 - Kayaking
18:00 - Nghỉ đêm trên tàu"></textarea>
                    <small class="text-muted">Ghi chi tiết từng hoạt động theo thời gian</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">🏨 Chỗ nghỉ</label>
                        <input type="text" name="accommodation" class="form-control" 
                               placeholder="VD: Khách sạn Mường Thanh 4 sao">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">🍽️ Bữa ăn</label>
                        <input type="text" name="meals" class="form-control" 
                               placeholder="VD: Sáng, Trưa, Tối">
                        <small class="text-muted">Các bữa ăn trong ngày</small>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Lưu lịch trình
                    </button>
                    <a href="index.php?act=admin-itinerary&tour_id=<?= $tour['id'] ?>" 
                       class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">