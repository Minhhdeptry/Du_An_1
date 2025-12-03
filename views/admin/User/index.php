<div class="container mt-4">
    <h2 class="mb-3">Quản lý Khách hàng</h2>

    <div class="d-flex justify-content-between align-items-center mb-3">

    <!-- Form tìm kiếm -->
    <form action="index.php" method="GET" class="form-inline">
        <input type="hidden" name="controller" value="user">
        <input type="hidden" name="action" value="index">
        <input type="text" name="keyword" class="form-control mr-2" placeholder="Tìm theo tên hoặc sđt"
               value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
        <button class="btn btn-primary mr-2">Tìm kiếm</button>
        <?php if (!empty($_GET['keyword'])): ?>
            <a href="index.php?act=admin-user" class="btn btn-secondary">Xóa</a>
        <?php endif; ?>
    </form>

    <!-- Nút thêm khách hàng -->
    <a href="index.php?act=admin-user-create" class="btn btn-primary">Thêm khách hàng</a>

</div>


    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>STT</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $i => $u): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($u['full_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone']) ?></td>
                        <td>
                            <a href="index.php?act=admin-user-edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                            <a href="index.php?act=admin-user-delete&id=<?= $u['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Bạn có chắc muốn xóa khách này không?');">Xóa</a>
                            <a href="index.php?act=admin-user-history&id=<?= $u['id'] ?>" class="btn btn-sm btn-info">Lịch sử đặt tour</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">Chưa có khách hàng nào</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
