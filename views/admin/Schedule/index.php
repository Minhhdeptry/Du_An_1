<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075);
    }

    .table-hover thead tr:hover {
        background-color: #343a40 !important;
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
                        <th>Loại / Ghế</th>
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
                            <td>
                                <strong><?= htmlspecialchars($s['tour_title'] ?? 'Chưa có') ?></strong>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($s['tour_code'] ?? '') ?></small>
                            </td>
                            <td><?= htmlspecialchars($s['category_name'] ?? 'Chưa có') ?></td>
                            <td><?= date('d/m/Y', strtotime($s['depart_date'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($s['return_date'])) ?></td>
                            <td>
                                <?php 
                                $tourType = $s['tour_type'] ?? 'REGULAR';
                                $isOnDemand = ($tourType === 'ON_DEMAND' || $tourType === 'Tour theo yêu cầu' || $s['seats_total'] == 0);
                                ?>
                                <?php if ($isOnDemand): ?>
                                    <span class="badge bg-info text-white">
                                        <i class="bi bi-infinity"></i> Theo yêu cầu
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary text-white">Tour thường</span>
                                    <br>
                                    <small class="text-muted">
                                        Còn: <strong><?= $s['seats_available'] ?></strong> / <?= $s['seats_total'] ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($s['price_adult'] ?: 0) ?>đ</td>
                            <td><?= number_format($s['price_children'] ?: 0) ?>đ</td>
                            <td>
                                <?php
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
                                <div class="d-flex flex-column gap-1">
                                    <!-- HDV chính -->
                                    <?php if (!empty($s['guide_id'])): ?>
                                        <div class="d-flex align-items-center justify-content-between bg-success p-1 rounded"
                                            style="--bs-bg-opacity: .2;">
                                            <small class="text-white fw-semibold">
                                                <i class="bi bi-person-fill"></i>
                                                <?= htmlspecialchars($s['guide_name'] ?? 'HDV chính') ?>
                                            </small>
                                            <a href="index.php?act=admin-staff-remove-guide&schedule_id=<?= $s['id'] ?>&type=guide"
                                                class="btn btn-sm btn-danger p-1"
                                                onclick="return confirm('Hủy phân công HDV chính?')"
                                                title="Hủy phân công HDV chính">
                                                <i class="bi bi-trash3 text-white"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <!-- HDV phụ -->
                                    <?php if (!empty($s['assistant_guide_id'])): ?>
                                        <div class="d-flex align-items-center justify-content-between bg-warning p-1 rounded"
                                            style="--bs-bg-opacity: .2;">
                                            <small class="text-white fw-semibold">
                                                <i class="bi bi-person"></i>
                                                <?= htmlspecialchars($s['assistant_name'] ?? 'HDV phụ') ?>
                                            </small>
                                            <a href="index.php?act=admin-staff-remove-guide&schedule_id=<?= $s['id'] ?>&type=assistant"
                                                class="btn btn-sm btn-danger p-1"
                                                onclick="return confirm('Hủy phân công HDV phụ?')"
                                                title="Hủy phân công HDV phụ">
                                                <i class="bi bi-trash3 text-white"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-2 d-flex gap-1">
                                    <!-- Sửa -->
                                    <a href="index.php?act=admin-schedule-edit&id=<?= $s['id'] ?>"
                                        class="btn btn-sm btn-warning" title="Sửa lịch">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <!-- Xóa lịch -->
                                    <a href="index.php?act=admin-schedule-delete&id=<?= $s['id'] ?>"
                                        class="btn btn-sm btn-danger" onclick="return confirm('Xóa lịch này?')"
                                        title="Xóa lịch">
                                        <i class="bi bi-trash3"></i>
                                    </a>

                                    <!-- Phân công HDV -->
                                    <a href="index.php?act=admin-staff-assign-form&schedule_id=<?= $s['id'] ?>"
                                        class="btn btn-sm btn-info" title="Phân công HDV">
                                        <i class="bi bi-person-plus"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">