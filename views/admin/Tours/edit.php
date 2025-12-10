<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="page-title">✏️ Sửa Tour</h2>
            <a href="index.php?act=admin-tour" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>

        <form method="post" action="index.php?act=admin-tour-update" enctype="multipart/form-data"
            class="card p-4 shadow">

            <input type="hidden" name="id" value="<?= $tour['id'] ?>">

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Mã Tour</label>
                    <input name="code" class="form-control" value="<?= $tour['code'] ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Giá người lớn</label>
                    <input type="number" name="adult_price" class="form-control" value="<?= $tour['adult_price'] ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Giá trẻ em</label>
                    <input type="number" name="child_price" class="form-control" value="<?= $tour['child_price'] ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Tiêu đề</label>
                <input name="title" class="form-control" value="<?= $tour['title'] ?>">
            </div>

            <div class="row mb-3">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Số ngày</label>
                        <input type="number" name="duration_days" class="form-control"
                            value="<?= $tour['duration_days'] ?>">
                    </div>
                    <!-- ✅ THÊM TRƯỜNG SỐ GHẾ -->
                    <div class="col-md-4">
                        <label class="form-label">Số ghế mặc định</label>
                        <input type="number" name="default_seats" class="form-control"
                            value="<?= $tour['default_seats'] ?? 30 ?>" min="1">
                        <small class="text-muted">Số ghế mặc định cho tour này</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Danh mục Tour</label>
                        <select name="category_id" class="form-control">
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $tour['category_id'] ? "selected" : "" ?>>
                                    <?= $c['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Mô tả ngắn</label>
                <textarea name="short_desc" class="form-control"><?= $tour['short_desc'] ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Mô tả đầy đủ</label>
                <textarea name="full_desc" class="form-control"><?= $tour['full_desc'] ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Chính sách</label>
                <textarea name="policy" class="form-control"><?= $tour['policy'] ?></textarea>
            </div>

            <!-- Ảnh hiện tại -->
            <div class="mb-3">
                <label class="form-label">Ảnh hiện tại</label><br>
                <?php
                $image = trim($tour['image_url'] ?? '');
                if ($image === '') {
                    echo '<p class="text-muted">Không có ảnh</p>';
                } else {
                    if (preg_match('#^(https?:)?//#i', $image) || str_contains($image, 'assets/')) {
                        $src = $image;
                    } else {
                        $src = 'assets/images/' . $image;
                    }
                    echo '<img src="' . htmlspecialchars($src) . '" width="150" class="mb-2 rounded">';
                }
                ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Chọn ảnh mới (nếu muốn thay)</label>
                <input type="file" name="image_file" class="form-control">
            </div>
            <input type="hidden" name="old_image" value="<?= $tour['image_url'] ?>">

            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="is_active" class="form-select">
                    <option value="1" <?= $tour['is_active'] ? "selected" : "" ?>>Hiển thị</option>
                    <option value="0" <?= !$tour['is_active'] ? "selected" : "" ?>>Ẩn</option>
                </select>
            </div>

            <button class="btn btn-primary">Cập nhật</button>
            <a href="index.php?act=admin-tour" class="btn btn-secondary">Hủy</a>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>