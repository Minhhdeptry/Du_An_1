<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Tour</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .table-img {
            width: 80px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            transition: transform 0.2s;
        }

        .table-img:hover {
            transform: scale(1.1);
        }

        .card {
            border-radius: 12px;
        }

        .page-title {
            font-weight: 600;
            font-size: 1.5rem;
        }

        .btn-sm {
            min-width: 60px;
        }

        .table thead th {
            vertical-align: middle;
            text-align: center;
        }

        .table tbody td {
            vertical-align: middle;
            text-align: center;
        }

        .search-form .form-control {
            min-width: 250px;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="page-title">📋 Danh sách Tour</h1>
            <a href="index.php?act=admin-tour-create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm Tour
            </a>
        </div>

        <!-- Form tìm kiếm -->
        <form class="row g-2 mb-4 search-form" method="get" action="index.php">
            <input type="hidden" name="act" value="admin-tour">
            <div class="col-auto">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm theo tên, mã tour..."
                    value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary">Tìm kiếm</button>
            </div>
            <?php if (!empty($_GET['keyword'])): ?>
                <div class="col-auto">
                    <a href="index.php?act=admin-tour" class="btn btn-secondary">Xóa</a>
                </div>
            <?php endif; ?>
        </form>

        <!-- Bảng tour -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>STT</th>
                            <th>Mã Tour</th>
                            <th>Tiêu đề</th>
                            <th>Danh mục</th> <!-- Thêm -->
                            <th>Giá người lớn</th>
                            <th>Giá trẻ em</th>
                            <th>Ngày</th>
                            <th>Ảnh</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tours as $key => $t): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td><?= $t["code"] ?></td>
                                <td class="text-start"><?= $t["title"] ?></td>
                                <td>
                                    <?= $t["category_name"] ?? 'Chưa có' ?> <!-- Hiển thị tên danh mục -->
                                </td>
                                <td><?= number_format($t["adult_price"]) ?> đ</td>
                                <td><?= number_format($t["child_price"]) ?> đ</td>
                                <td><?= $t["duration_days"] ?> ngày</td>
                                <td>
                                    <?php
                                    $image = trim($t['image_url'] ?? '');
                                    if ($image === '') {
                                        echo '<span class="text-muted">Không có ảnh</span>';
                                    } else {
                                        if (preg_match('#^(https?:)?//#i', $image) || str_starts_with($image, '/') || str_contains($image, 'assets/')) {
                                            $src = $image;
                                        } else {
                                            $src = 'assets/images/' . $image;
                                        }
                                        $serverPath = __DIR__ . '/../../../' . ltrim($src, '/');
                                        if (file_exists($serverPath)) {
                                            echo '<img src="' . htmlspecialchars($src) . '" class="table-img" alt="">';
                                        } else {
                                            echo '<span class="text-muted">Ảnh không tìm thấy</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span
                                        class="badge <?= $t['display_status'] === 'Hiển thị' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $t['display_status'] ?>
                                    </span>
                                </td>

                                <!-- Thay thế phần "Hành động" trong views/admin/Tours/index.php -->

                                <td>
                                    <a href="index.php?act=admin-tour-detail&id=<?= $t['id'] ?>"
                                        class="btn btn-sm btn-primary me-1 mb-1" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i> Chi tiết
                                    </a>
                                    <a href="index.php?act=admin-itinerary&tour_id=<?= $t['id'] ?>"
                                        class="btn btn-sm btn-info me-1 mb-1" title="Quản lý lịch trình theo ngày">
                                        <i class="bi bi-calendar-week"></i> Lịch trình
                                    </a>
                                    <a href="index.php?act=admin-tour-edit&id=<?= $t['id'] ?>"
                                        class="btn btn-sm btn-warning me-1 mb-1">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>
                                    <a href="index.php?act=admin-tour-delete&id=<?= $t['id'] ?>"
                                        onclick="return confirm('Bạn có chắc muốn xóa tour này?')"
                                        class="btn btn-sm btn-danger mb-1">
                                        <i class="bi bi-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS + Icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>

</html>