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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="page-title">➕ Thêm mới Tour</h2>
            <a href="index.php?act=admin-tour" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <form method="post" action="index.php?act=admin-tour-store" enctype="multipart/form-data">

                    <div class="form-group">
                        <label>Tiêu đề</label>
                        <input name="title" class="form-control" placeholder="Tên tour">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Mã Tour</label>
                            <input name="code" class="form-control" placeholder="Mã tour">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Giá người lớn</label>
                            <input type="number" name="adult_price" class="form-control" placeholder="0,00đ">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Giá trẻ em</label>
                            <input type="number" name="child_price" class="form-control" placeholder="0,00đ">
                        </div>
                    </div>

                    <!-- ✅ THÊM DÒNG NÀY -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Số ghế (mặc định cho tour)</label>
                            <input type="number" name="default_seats" class="form-control" placeholder="VD: 30" min="1"
                                value="30">
                            <small class="text-muted">Số ghế mặc định khi tạo lịch khởi hành cho tour này</small>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Số ngày</label>
                                <input type="number" name="duration_days" class="form-control" placeholder="VD: 3">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Danh mục</label>
                                <select name="category_id" class="form-control">
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="form-group">
                        <label>Mô tả ngắn</label>
                        <textarea name="short_desc" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Mô tả đầy đủ</label>
                        <textarea name="full_desc" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Chính sách</label>
                        <textarea name="policy" class="form-control" rows="2"></textarea>
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

                    <div>
                        <button type="submit" class="btn btn-primary">Thêm mới</button>
                        <a href="index.php?act=admin-tour" class="btn btn-secondary">Hủy</a>
                    </div>

                </form>

            </div>
        </div>

    </div>

</body>

</html>