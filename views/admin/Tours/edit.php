<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Tour</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">

    <h2 class="mb-4">Sửa Tour</h2>

    <form method="post" action="index.php?act=admin-tour-update" enctype="multipart/form-data" class="card p-4 shadow">

        <input type="hidden" name="id" value="<?= $tour['id'] ?>">

        <div class="form-group">
            <label>Mã Tour</label>
            <input name="code" class="form-control" value="<?= $tour['code'] ?>">
        </div>

        <div class="form-group">
            <label>Tiêu đề</label>
            <input name="title" class="form-control" value="<?= $tour['title'] ?>">
        </div>

        <div class="form-group">
            <label>Mô tả ngắn</label>
            <textarea name="short_desc" class="form-control"><?= $tour['short_desc'] ?></textarea>
        </div>

        <div class="form-group">
            <label>Mô tả đầy đủ</label>
            <textarea name="full_desc" class="form-control"><?= $tour['full_desc'] ?></textarea>
        </div>

        <div class="form-group">
            <label>Giá</label>
            <input type="number" name="base_price" class="form-control" value="<?= $tour['base_price'] ?>">
        </div>

        <div class="form-group">
            <label>Số ngày</label>
            <input type="number" name="duration_days" class="form-control" value="<?= $tour['duration_days'] ?>">
        </div>

        <!-- Danh mục -->
        <div class="form-group">
            <label>Danh mục Tour</label>
            <select name="category_id" class="form-control">
                <?php foreach ($cats as $c): ?>
                    <option value="<?= $c['id'] ?>" 
                        <?= $c['id'] == $tour['category_id'] ? "selected" : "" ?>>
                        <?= $c['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Chính sách</label>
            <textarea name="policy" class="form-control"><?= $tour['policy'] ?></textarea>
        </div>

        <div class="form-group">
            <label>Nhà cung cấp</label>
            <input name="supplier" class="form-control" value="<?= $tour['supplier'] ?>">
        </div>

        <div class="form-group">
            <label>Ảnh hiện tại</label><br>
            <?php if ($tour["image_url"]): ?>
                <img src="assets/images/<?= $tour['image_url'] ?>" width="150" class="mb-2 rounded">
            <?php else: ?>
                <p class="text-muted">Không có ảnh</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Chọn ảnh mới (nếu muốn thay)</label>
            <input type="file" name="image_file" class="form-control-file">
        </div>

        <input type="hidden" name="old_image" value="<?= $tour['image_url'] ?>">

        <div class="form-group">
            <label>Trạng thái</label>
            <select name="is_active" class="form-control">
                <option value="1" <?= $tour['is_active'] ? "selected" : "" ?>>Hiển thị</option>
                <option value="0" <?= !$tour['is_active'] ? "selected" : "" ?>>Ẩn</option>
            </select>
        </div>

        <button class="btn btn-primary">Cập nhật</button>
        <a href="index.php?act=admin-tour" class="btn btn-secondary">Hủy</a>
    </form>
</div>

</body>
</html>
