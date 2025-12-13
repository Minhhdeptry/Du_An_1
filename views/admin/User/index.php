<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075);
    }

    .table-hover thead tr:hover {
        background-color: #343a40 !important;
        /* giữ màu dark cho thead */
    }
</style>
<div class="container-fluid px-4 mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title"><i class="bi bi-people"></i> Quản lý Khách hàng</h2>
        <a href="index.php?act=admin-user-create" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Thêm khách hàng
        </a>
    </div>

    <!-- Form tìm kiếm -->
    <form class="row g-2 mb-4" method="get" action="index.php">
        <input type="hidden" name="act" value="admin-user">
        <div class="col-auto">
            <input type="text" name="keyword" class="form-control" placeholder="Tìm theo tên, số điện thoại..."
                value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary"><i class="bi bi-search"></i> Tìm kiếm</button>
        </div>
        <?php if (!empty($_GET['keyword'])): ?>
            <div class="col-auto">
                <a href="index.php?act=admin-user" class="btn btn-secondary">Xóa</a>
            </div>
        <?php endif; ?>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Số tour đã đặt</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $i => $u): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="text-start"><?= htmlspecialchars($u['full_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['phone'] ?? '') ?></td>
                                <td class="text-center"><?= htmlspecialchars($u['total_bookings'] ?? 0) ?></td>


                                <td>
                                    <a href="index.php?act=admin-user-edit&id=<?= $u['id'] ?>"
                                        class="btn btn-sm btn-warning me-1 mb-1">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>
                                    <a href="index.php?act=admin-user-delete&id=<?= $u['id'] ?>"
                                        onclick="return confirm('Bạn có chắc muốn xóa khách này không?')"
                                        class="btn btn-sm btn-danger me-1 mb-1">
                                        <i class="bi bi-trash"></i> Xóa
                                    </a>
                                    <a href="index.php?act=admin-user-history&id=<?= $u['id'] ?>"
                                        class="btn btn-sm btn-info mb-1">
                                        <i class="bi bi-journal-text"></i> Lịch sử
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Chưa có khách hàng nào
                            </td>
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