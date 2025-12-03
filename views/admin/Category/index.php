<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục Tour</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="page-title">Danh mục Tour</h1>

            <a href="index.php?act=admin-category-create" class="btn btn-primary">
                + Thêm danh mục
            </a>
        </div>

        <?php if (!isset($categories) || !is_array($categories))
            $categories = []; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">

                <table class="table table-striped table-bordered mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="5%">ID</th>
                            <th width="10%">Mã</th>
                            <th width="25%">Tên danh mục</th>
                            <th width="30%">Ghi chú</th>
                            <th width="10%">Trạng thái</th>
                            <th width="20%">Hành động</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($categories) == 0): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i>Chưa có danh mục nào</i>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td><?= $c["id"] ?></td>
                                <td><?= $c["code"] ?></td>
                                <td><?= $c["name"] ?></td>
                                <td><?= $c["note"] ?></td>

                                <td>
                                    <span class="badge badge-<?= $c["is_active"] ? 'success' : 'secondary' ?>">
                                        <?= $c["is_active"] ? "Hiển thị" : "Ẩn" ?>
                                    </span>
                                </td>

                                <td>
                                    <a href="index.php?act=admin-category-edit&id=<?= $c['id'] ?>"
                                        class="btn btn-sm btn-warning">
                                        Sửa
                                    </a>

                                    <a href="index.php?act=admin-category-delete&id=<?= $c['id'] ?>"
                                        onclick="return confirm('Bạn có chắc muốn xóa danh mục này?')"
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