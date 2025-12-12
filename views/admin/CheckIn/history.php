<?php
// ============================================
// FILE 4: views/admin/CheckIn/history.php
// ============================================
?>

<div class="container mt-4">
    <h2 class="mb-4"><i class="bi bi-clock-history"></i> Lịch sử Check-in</h2>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="act" value="admin-checkin-history">
                
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
                
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="PRESENT" <?= ($status == 'PRESENT') ? 'selected' : '' ?>>Có mặt</option>
                        <option value="ABSENT" <?= ($status == 'ABSENT') ? 'selected' : '' ?>>Vắng</option>
                        <option value="LATE" <?= ($status == 'LATE') ? 'selected' : '' ?>>Muộn</option>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Ngày</th>
                        <th>Booking</th>
                        <th>Tour</th>
                        <th>Khách</th>
                        <th>Trạng thái</th>
                        <th>Check-in bởi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($checkIns as $c): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($c['check_in_time'])) ?></td>
                            <td><code><?= htmlspecialchars($c['booking_code']) ?></code></td>
                            <td><?= htmlspecialchars($c['tour_name']) ?></td>
                            <td><?= htmlspecialchars($c['guest_name']) ?></td>
                            <td>
                                <span class="badge bg-<?= $c['status'] == 'PRESENT' ? 'success' : 'warning' ?>">
                                    <?= $c['status'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($c['admin_name'] ?? 'System') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>