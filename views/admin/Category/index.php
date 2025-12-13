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

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075);
    }

    .table-hover thead tr:hover {
        background-color: #343a40 !important;
        /* giữ màu dark cho thead */
    }




    .search-form .form-control {
        min-width: 250px;
    }
</style>
<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title">📋 Danh mục Tour</h1>
        <a href="index.php?act=admin-category-create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Thêm danh mục
        </a>
    </div>

    <!-- Form tìm kiếm -->
    <form class="row g-2 mb-4" method="get" action="index.php">
        <input type="hidden" name="act" value="admin-category">
        <div class="col-auto">
            <input type="text" name="keyword" class="form-control" placeholder="Tìm theo tên, mã danh mục..."
                value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary"><i class="bi bi-search"></i> Tìm kiếm</button>
        </div>
        <?php if (!empty($_GET['keyword'])): ?>
            <div class="col-auto">
                <a href="index.php?act=admin-category" class="btn btn-secondary">Xóa</a>
            </div>
        <?php endif; ?>
    </form>

    <!-- Bảng danh mục -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>STT</th>
                        <th>Mã</th>
                        <th>Tên danh mục</th>
                        <th>Ghi chú</th>
                        <th>Số tour</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $key => $c): ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= $c['code'] ?></td>
                            <td class="text-start"><?= $c['name'] ?></td>

                            <td class="text-start"><?= $c['note'] ?></td>
                            <td><?= $c['tour_count'] ?></td>
                            <td>
                                <span class="badge <?= $c["is_active"] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $c["is_active"] ? "Hiển thị" : "Ẩn" ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?act=admin-category-edit&id=<?= $c['id'] ?>"
                                    class="btn btn-sm btn-warning me-1 mb-1">
                                    <i class="bi bi-pencil"></i> Sửa
                                </a>

                                <?php if ($c['tour_count'] == 0): ?>
                                    <a href="index.php?act=admin-category-delete&id=<?= $c['id'] ?>"
                                        class="btn btn-sm btn-danger mb-1"
                                        onclick="return confirm('Bạn có chắc muốn xóa danh mục này?')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary mb-1" disabled>
                                        <i class="bi bi-x-circle"></i> Không thể xóa
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($categories) == 0): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Chưa có danh mục nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap JS + Icons -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">