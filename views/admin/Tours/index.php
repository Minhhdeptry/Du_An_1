<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Tour</title>

    <!-- BOOTSTRAP -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title">Danh sách Tour</h1>

        <a href="index.php?act=admin-tour-create" class="btn btn-primary">
            + Thêm Tour
        </a>
    </div>

    <!-- Nếu $tours chưa có -> gán mảng rỗng để tránh lỗi -->
    <?php if (!isset($tours) || !is_array($tours)) $tours = []; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <table class="table table-striped table-bordered mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th width="5%">ID</th>
                        <th width="10%">Mã</th>
                        <th width="25%">Tiêu đề</th>
                        <th width="10%">Giá</th>
                        <th width="10%">Ngày</th>
                        <th width="15%">Ảnh</th>
                        <th width="10%">Trạng thái</th>
                        <th width="15%">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (count($tours) == 0): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i>Chưa có tour nào</i>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($tours as $t): ?>
                    <tr>
                        <td><?= $t["id"] ?></td>
                        <td><?= $t["code"] ?></td>
                        <td><?= $t["title"] ?></td>
                        <td><?= number_format($t["base_price"]) ?>đ</td>
                        <td><?= $t["duration_days"] ?> ngày</td>

                        <td>
                            <?php
                            // đảm bảo $t['image_url'] tồn tại
                            $image = trim($t['image_url'] ?? '');

                            // nếu rỗng -> hiển thị text
                            if ($image === '') {
                                echo '<span class="text-muted">Không có ảnh</span>';
                            } else {
                                // Nếu người dùng đã lưu toàn bộ đường dẫn (ví dụ "assets/images/halong.jpg" hoặc "/assets/..")
                                if (preg_match('#^(https?:)?//#i', $image) || str_starts_with($image, '/') || str_contains($image, 'assets/')) {
                                    $src = $image;
                                } else {
                                    // nếu chỉ lưu filename, nối vào folder assets/images
                                    $src = 'assets/images/' . $image;
                                }

                                // kiểm tra file thực tế tồn tại trên server (tùy chọn)
                                $serverPath = __DIR__ . '/../../../' . ltrim($src, '/'); // điều chỉnh nếu cấu trúc folder khác
                                if (file_exists($serverPath)) {
                                    // escape URL an toàn
                                    echo '<img src="'.htmlspecialchars($src).'" width="90" class="rounded" alt="">';
                                } else {
                                    // nếu file server không có, vẫn in link (trong dev check) hoặc hiển thị placeholder
                                    // echo '<img src="'.htmlspecialchars($src).'" width="90" class="rounded" alt="">';
                                    echo '<span class="text-muted">Ảnh không tìm thấy</span>';
                                }
                            }
                            ?>
                        </td>


                        <td>
                            <span class="badge badge-<?= $t["is_active"] ? 'success' : 'secondary' ?>">
                                <?= $t["is_active"] ? "Hiển thị" : "Ẩn" ?>
                            </span>
                        </td>

                        <td>
                            <a href="index.php?act=admin-tour-edit&id=<?= $t['id'] ?>" 
                               class="btn btn-sm btn-warning">
                                Sửa
                            </a>
                            
                            <a href="index.php?act=admin-tour-delete&id=<?= $t['id'] ?>"
                               onclick="return confirm('Bạn có chắc muốn xóa tour này?')"
                               class="btn btn-sm btn-danger">
                                Xóa
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>

        </div>
    </div>

</div>

</body>
</html>