<?php
// ============================================
// FILE 4: views/admin/TourReport/list.php
// ============================================
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-list"></i> Tất cả Báo cáo Tour
            </h2>
            <p class="text-muted mb-0">
                Tổng: <strong><?= count($reports) ?></strong> báo cáo
            </p>
        </div>
        <a href="?act=admin-tour-reports" class="btn btn-warning">
            <i class="bi bi-exclamation-circle"></i> Tour cần báo cáo
        </a>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="act" value="admin-tour-reports-all">
                
                <div class="col-md-3">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="from_date" class="form-control" 
                           value="<?= htmlspecialchars($from_date) ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="to_date" class="form-control" 
                           value="<?= htmlspecialchars($to_date) ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">HDV</label>
                    <select name="guide_id" class="form-select">
                        <option value="">Tất cả HDV</option>
                        <!-- Populate with guides -->
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Ngày tour</th>
                        <th>Tên tour</th>
                        <th>HDV</th>
                        <th>Khách thực tế</th>
                        <th>Đánh giá</th>
                        <th>Người tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $r): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($r['depart_date'])) ?></td>
                            <td><?= htmlspecialchars($r['tour_name']) ?></td>
                            <td><?= htmlspecialchars($r['guide_name'] ?? 'N/A') ?></td>
                            <td class="text-center">
                                <strong class="text-success"><?= $r['actual_guests'] ?></strong>
                            </td>
                            <td>
                                <span class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?= $i <= ($r['overall_rating'] ?? 0) ? '⭐' : '☆' ?>
                                    <?php endfor; ?>
                                </span>
                            </td>
                            <td><small><?= htmlspecialchars($r['created_by_name'] ?? 'System') ?></small></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="?act=admin-tour-report-view&id=<?= $r['id'] ?>" 
                                       class="btn btn-info" title="Xem">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="?act=admin-tour-report-create&schedule_id=<?= $r['schedule_id'] ?>" 
                                       class="btn btn-warning" title="Sửa">
                                        <i class="bi bi-pencil"></i>
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