
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h2>Quản lý Nhân viên</h2>
        <a href="index.php?act=admin-staff-create" class="btn btn-primary">
            + Thêm nhân viên
        </a>
    </div>
    

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-dark bg-dark text-white">
                    <tr>
                        <th>#</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>CMND/CCCD</th>
                        <th>Trình độ</th>
                        <th>Trạng thái</th>
                        <th width="140">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($staffs)): ?>
                        <?php foreach ($staffs as $i => $s): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= $s["full_name"] ?></td>
                                <td><?= $s["email"] ?></td>
                                <td><?= $s["phone"] ?></td>
                                <td><?= $s["id_number"] ?></td>
                                <td><?= $s["qualification"] ?></td>
                                <td>
                                    <?php if ($s["status"] == "ACTIVE"): ?>
                                        <span class="badge bg-success">Đang làm</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Nghỉ</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="index.php?act=admin-staff-edit&id=<?= $s['id'] ?>" 
                                       class="btn btn-sm btn-warning">Sửa</a>
                                    <a onclick="return confirm('Xoá nhân viên này?')" 
                                       href="index.php?act=admin-staff-delete&id=<?= $s['id'] ?>"
                                       class="btn btn-sm btn-danger">Xoá</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Chưa có nhân viên nào.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>

        </div>
    </div>
</div>
