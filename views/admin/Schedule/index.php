<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075);
    }

    .table-hover thead tr:hover {
        background-color: #343a40 !important;
        /* giữ màu dark cho thead */
    }
</style>
<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">📅 Lịch khởi hành</h3>
        <a href="index.php?act=admin-schedule-create" class="btn btn-primary shadow-sm">+ Tạo lịch</a>
    </div>

    <form class="row g-2 mb-3" method="get" action="index.php">
        <input type="hidden" name="act" value="admin-schedule">
        <div class="col-auto">
            <input type="text" name="keyword" class="form-control" placeholder="Tìm tour, mã tour, ngày, danh mục..."
                value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Tìm kiếm</button>
        </div>
        <?php if (!empty($_GET['keyword'])): ?>
            <div class="col-auto">
                <a href="index.php?act=admin-schedule" class="btn btn-secondary">Xóa</a>
            </div>
        <?php endif; ?>
    </form>


    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>STT</th>
                        <th>Tour</th>
                        <th>Danh mục</th>
                        <th>Ngày đi</th>
                        <th>Ngày về</th>
                        <th>Ghế / Còn</th>
                        <th>Giá người lớn</th>
                        <th>Giá trẻ em</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $key => $s): ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= htmlspecialchars($s['tour_title']) ?></td>
                            <td><?= htmlspecialchars($s['category_name'] ?? 'Chưa có') ?></td>
                            <td><?= date('d/m/Y', strtotime($s['depart_date'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($s['return_date'])) ?></td>
                            <td>
                                <?= $s['seats_available'] ?> / <?= $s['seats_total'] ?>
                            </td>
                            <td><?= number_format($s['price_adult'] ?: 0) ?>đ</td>
                            <td><?= number_format($s['price_children'] ?: 0) ?>đ</td>
                            <td>
                                <?php
                                // Map trạng thái enum sang text tiếng Việt và class màu
                                $statusMap = [
                                    'OPEN' => ['text' => 'Mở đăng ký', 'class' => 'bg-primary text-white'],
                                    'CLOSED' => ['text' => 'Đã đóng', 'class' => 'bg-warning text-dark'],
                                    'CANCELED' => ['text' => 'Đã hủy', 'class' => 'bg-danger text-white'],
                                    'FINISHED' => ['text' => 'Hoàn tất', 'class' => 'bg-success text-white'],
                                ];
                                $status = $statusMap[$s['status']] ?? ['text' => $s['status'], 'class' => 'bg-secondary text-white'];
                                ?>
                                <span class="badge <?= $status['class'] ?>"><?= $status['text'] ?></span>
                            </td>

                            <td>
                                <a href="index.php?act=admin-schedule-edit&id=<?= $s['id'] ?>"
                                    class="btn btn-sm btn-warning">Sửa</a>
                                <a href="index.php?act=admin-schedule-delete&id=<?= $s['id'] ?>"
                                    onclick="return confirm('Xóa lịch này?')" class="btn btn-sm btn-danger">Xóa</a>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>