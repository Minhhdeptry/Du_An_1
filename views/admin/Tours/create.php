<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Tour</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">

    <h2 class="mb-4">Thêm Tour</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="post" action="index.php?act=admin-tour-store" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Mã Tour</label>
                    <input name="code" class="form-control">
                </div>

                <div class="form-group">
                    <label>Tiêu đề</label>
                    <input name="title" class="form-control">
                </div>

                <div class="form-group">
                    <label>Mô tả ngắn</label>
                    <textarea name="short_desc" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Mô tả đầy đủ</label>
                    <textarea name="full_desc" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Giá</label>
                    <input type="number" name="base_price" class="form-control">
                </div>

                <div class="form-group">
                    <label>Số ngày</label>
                    <input type="number" name="duration_days" class="form-control">
                </div>

                <!-- SELECT CATEGORY -->
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="category_id" class="form-control">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Chính sách</label>
                    <textarea name="policy" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Nhà cung cấp</label>
                    <input name="supplier" class="form-control">
                </div>

                <div class="form-group">
                    <label>Ảnh đại diện</label>
                    <input type="file" name="image_file" class="form-control-file">
                    <small class="text-muted">Chọn ảnh từ máy (jpg, png, webp)</small>
                </div>

                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="is_active" class="form-control">
                        <option value="1">Hiển thị</option>
                        <option value="0">Ẩn</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Lưu</button>
                <a href="index.php?act=admin-tour" class="btn btn-secondary">Hủy</a>

            </form>

        </div>
    </div>

</div>

</body>
</html>
