<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-bus"></i> Tours Được Phân Công
            </h2>
            <p class="text-muted mb-0">
                Hướng dẫn viên: <strong><?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'HDV') ?></strong>
            </p>
        </div>
        <div>
            <a href="?act=guide-dashboard" class="btn btn-outline-primary">
                <i class="fas fa-chart-line"></i> Thống kê cá nhân
            </a>
        </div>
    </div>

    <?php
    $totalTours = count($assignedTours ?? []);
    $upcomingTours = 0;
    $ongoingTours = 0;
    $finishedTours = 0;
    
    foreach ($assignedTours as $tour) {
        $today = date('Y-m-d');
        if ($tour['status'] === 'FINISHED') {
            $finishedTours++;
        } elseif ($tour['depart_date'] > $today) {
            $upcomingTours++;
        } elseif ($tour['depart_date'] <= $today && $tour['return_date'] >= $today) {
            $ongoingTours++;
        }
    }
    ?>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-uppercase">Tổng số tour</h6>
                    <h2 class="mb-0"><?= $totalTours ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-uppercase">Sắp khởi hành</h6>
                    <h2 class="mb-0"><?= $upcomingTours ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-uppercase">Đang diễn ra</h6>
                    <h2 class="mb-0"><?= $ongoingTours ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-uppercase">Đã hoàn thành</h6>
                    <h2 class="mb-0"><?= $finishedTours ?></h2>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($assignedTours)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-info-circle fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Chưa có tour nào được phân công</h4>
                <p class="text-muted mb-4">
                    Hiện tại bạn chưa được phân công dẫn tour nào. <br>
                    Vui lòng liên hệ quản trị viên để được phân công lịch.
                </p>
                <a href="?act=logout" class="btn btn-outline-secondary">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </div>
        </div>
    <?php else: ?>
        <ul class="nav nav-tabs mb-3" id="tourTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">
                    <i class="fas fa-list"></i> Tất cả (<?= $totalTours ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button">
                    <i class="fas fa-clock"></i> Sắp khởi hành (<?= $upcomingTours ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ongoing-tab" data-bs-toggle="tab" data-bs-target="#ongoing" type="button">
                    <i class="fas fa-spinner"></i> Đang diễn ra (<?= $ongoingTours ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="finished-tab" data-bs-toggle="tab" data-bs-target="#finished" type="button">
                    <i class="fas fa-check"></i> Hoàn thành (<?= $finishedTours ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="tourTabsContent">
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                <?php renderTourTable($assignedTours); ?>
            </div>

            <div class="tab-pane fade" id="upcoming" role="tabpanel">
                <?php
                $upcomingList = array_filter($assignedTours, function($tour) {
                    return $tour['depart_date'] > date('Y-m-d');
                });
                renderTourTable($upcomingList);
                ?>
            </div>

            <div class="tab-pane fade" id="ongoing" role="tabpanel">
                <?php
                $ongoingList = array_filter($assignedTours, function($tour) {
                    $today = date('Y-m-d');
                    return $tour['depart_date'] <= $today && $tour['return_date'] >= $today && $tour['status'] !== 'FINISHED';
                });
                renderTourTable($ongoingList);
                ?>
            </div>

            <div class="tab-pane fade" id="finished" role="tabpanel">
                <?php
                $finishedList = array_filter($assignedTours, function($tour) {
                    return $tour['status'] === 'FINISHED';
                });
                renderTourTable($finishedList);
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
function renderTourTable($tours) {
    if (empty($tours)):
?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle"></i> Không có tour nào trong danh mục này
    </div>
<?php
        return;
    endif;
?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="30%">Tên Tour</th>
                        <th width="10%" class="text-center">Mã Tour</th>
                        <th width="12%" class="text-center">Ngày Khởi Hành</th>
                        <th width="12%" class="text-center">Ngày Kết Thúc</th>
                        <th width="8%" class="text-center">Số Ngày</th>
                        <th width="10%" class="text-center">Trạng Thái</th>
                        <th width="13%" class="text-center">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $index = 0;
                    foreach ($tours as $tour): 
                        $index++;
                        
                        $daysUntil = (strtotime($tour['depart_date']) - strtotime(date('Y-m-d'))) / 86400;
                        $rowClass = '';
                        if ($daysUntil <= 0 && $daysUntil >= -1) {
                            $rowClass = 'table-warning';
                        }
                    ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="text-center"><?= $index ?></td>
                            <td>
                                <strong class="text-primary"><?= htmlspecialchars($tour['tour_name']) ?></strong>
                                <?php if ($daysUntil > 0 && $daysUntil <= 3): ?>
                                    <br><small class="badge bg-warning text-dark">
                                        <i class="fas fa-exclamation-triangle"></i> Còn <?= floor($daysUntil) ?> ngày
                                    </small>
                                <?php elseif ($daysUntil <= 0 && $daysUntil >= -1): ?>
                                    <br><small class="badge bg-danger">
                                        <i class="fas fa-fire"></i> Đang khởi hành hôm nay!
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?= htmlspecialchars($tour['tour_code'] ?? '-') ?></span>
                            </td>
                            <td class="text-center">
                                <i class="fas fa-calendar-day text-success"></i>
                                <?= date('d/m/Y', strtotime($tour['depart_date'])) ?>
                            </td>
                            <td class="text-center">
                                <i class="fas fa-calendar-check text-danger"></i>
                                <?= date('d/m/Y', strtotime($tour['return_date'])) ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">
                                    <?= (strtotime($tour['return_date']) - strtotime($tour['depart_date'])) / 86400 ?> ngày
                                </span>
                            </td>
                            <td class="text-center">
                                <?php
                                $status = $tour['status'] ?? '';
                                $statusDisplay = match($status) {
                                    'OPEN' => ['label' => 'Mở', 'class' => 'bg-success', 'icon' => 'fa-door-open'],
                                    'CLOSED' => ['label' => 'Đóng', 'class' => 'bg-warning text-dark', 'icon' => 'fa-door-closed'],
                                    'FINISHED' => ['label' => 'Hoàn thành', 'class' => 'bg-secondary', 'icon' => 'fa-check-circle'],
                                    'CANCELED' => ['label' => 'Đã hủy', 'class' => 'bg-danger', 'icon' => 'fa-times-circle'],
                                    default => ['label' => 'Không xác định', 'class' => 'bg-light text-dark', 'icon' => 'fa-question-circle']
                                };
                                ?>
                                <span class="badge <?= $statusDisplay['class'] ?>">
                                    <i class="fas <?= $statusDisplay['icon'] ?>"></i>
                                    <?= $statusDisplay['label'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="?act=tour-detail&tour_schedule_id=<?= $tour['id'] ?>"
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
}
?>

<style>
.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 3px solid transparent;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #dee2e6;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom-color: #0d6efd;
    font-weight: 600;
}

.card {
    border-radius: 8px;
    overflow: hidden;
}

.badge {
    padding: 6px 12px;
    font-size: 0.85rem;
}

.table-warning {
    background-color: #fff3cd !important;
}
</style>