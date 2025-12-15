<div class="container-fluid mt-4">
    <?php
    $tour_schedule_id = $_GET['tour_schedule_id'] ?? null;
    
    if (!$tour_schedule_id) {
        echo '<div class="alert alert-danger">Không tìm thấy thông tin tour!</div>';
        return;
    }
    
    // Lấy thông tin tour schedule
    require_once "./commons/function.php";
    $pdo = connectDB();
    
    $stmtTour = $pdo->prepare("
        SELECT ts.*, t.title as tour_name, t.code as tour_code, t.duration_days,
               s1.id as guide_staff_id, u1.full_name as guide_name,
               s2.id as assistant_staff_id, u2.full_name as assistant_name
        FROM tour_schedule ts
        JOIN tours t ON ts.tour_id = t.id
        LEFT JOIN staffs s1 ON ts.guide_id = s1.id
        LEFT JOIN users u1 ON s1.user_id = u1.id
        LEFT JOIN staffs s2 ON ts.assistant_guide_id = s2.id
        LEFT JOIN users u2 ON s2.user_id = u2.id
        WHERE ts.id = ?
    ");
    $stmtTour->execute([$tour_schedule_id]);
    $tourInfo = $stmtTour->fetch(PDO::FETCH_ASSOC);
    
    if (!$tourInfo) {
        echo '<div class="alert alert-danger">Tour không tồn tại!</div>';
        return;
    }
    
    // Lấy danh sách khách
    $stmtCustomers = $pdo->prepare("
        SELECT bc.*, b.booking_code, b.contact_name, b.contact_phone
        FROM booking_customers bc
        JOIN bookings b ON bc.booking_id = b.id
        WHERE b.tour_schedule_id = ? AND b.status NOT IN ('CANCELED')
        ORDER BY b.booking_code, bc.customer_type, bc.full_name
    ");
    $stmtCustomers->execute([$tour_schedule_id]);
    $customers = $stmtCustomers->fetchAll(PDO::FETCH_ASSOC);
    
    // Thống kê check-in
    $totalCustomers = count($customers);
    $checkedInCount = 0;
    foreach ($customers as $c) {
        if ($c['checked_in']) $checkedInCount++;
    }
    $checkInPercent = $totalCustomers > 0 ? round(($checkedInCount / $totalCustomers) * 100) : 0;
    
    // Tổng số booking
    $stmtBookingCount = $pdo->prepare("
        SELECT COUNT(*) as total FROM bookings 
        WHERE tour_schedule_id = ? AND status NOT IN ('CANCELED')
    ");
    $stmtBookingCount->execute([$tour_schedule_id]);
    $totalBookings = $stmtBookingCount->fetch(PDO::FETCH_ASSOC)['total'];
    ?>

    <!-- Tour Info Card -->
    <div class="card border-primary mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-bus"></i> <?= htmlspecialchars($tourInfo['tour_name']) ?>
            </h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Mã tour:</strong> <?= htmlspecialchars($tourInfo['tour_code']) ?></p>
                    <p><strong>Ngày khởi hành:</strong> <?= date('d/m/Y', strtotime($tourInfo['depart_date'])) ?></p>
                    <p><strong>Ngày kết thúc:</strong> <?= date('d/m/Y', strtotime($tourInfo['return_date'])) ?></p>
                    <p><strong>Số ngày:</strong> <?= $tourInfo['duration_days'] ?> ngày</p>
                </div>
                <div class="col-md-6">
                    <p><strong>HDV chính:</strong> <?= $tourInfo['guide_name'] ? htmlspecialchars($tourInfo['guide_name']) : '<span class="text-muted">Chưa phân công</span>' ?></p>
                    <p><strong>HDV phụ:</strong> <?= $tourInfo['assistant_name'] ? htmlspecialchars($tourInfo['assistant_name']) : '<span class="text-muted">Chưa phân công</span>' ?></p>
                    <p><strong>Trạng thái:</strong> 
                        <?php
                        $statusBadge = match($tourInfo['status']) {
                            'OPEN' => '<span class="badge bg-success">Mở</span>',
                            'CLOSED' => '<span class="badge bg-warning">Đóng</span>',
                            'FINISHED' => '<span class="badge bg-secondary">Hoàn thành</span>',
                            'CANCELED' => '<span class="badge bg-danger">Đã hủy</span>',
                            default => '<span class="badge bg-light text-dark">Không xác định</span>'
                        };
                        echo $statusBadge;
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-info shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Tổng booking</h5>
                    <h2 class="mb-0"><?= $totalBookings ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Tổng khách</h5>
                    <h2 class="mb-0"><?= $totalCustomers ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Đã check-in</h5>
                    <h2 class="mb-0"><?= $checkedInCount ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Tỷ lệ check-in</h5>
                    <h2 class="mb-0"><?= $checkInPercent ?>%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Check-in Progress Bar -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Tiến độ check-in</h5>
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                     role="progressbar" 
                     style="width: <?= $checkInPercent ?>%;" 
                     aria-valuenow="<?= $checkInPercent ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    <?= $checkedInCount ?>/<?= $totalCustomers ?> (<?= $checkInPercent ?>%)
                </div>
            </div>
        </div>
    </div>

    <!-- Customer List -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users"></i> Danh sách khách hàng
                </h5>
                <button class="btn btn-success btn-sm" onclick="checkInAll()">
                    <i class="fas fa-check-double"></i> Check-in tất cả
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($customers)): ?>
                <div class="alert alert-info m-3 text-center">
                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                    <p class="mb-0">Chưa có khách nào trong tour này</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">#</th>
                                <th width="15%">Mã booking</th>
                                <th width="20%">Họ tên</th>
                                <th width="10%" class="text-center">Loại khách</th>
                                <th width="10%">SĐT</th>
                                <th width="10%">Ngày sinh</th>
                                <th width="15%">Ghi chú</th>
                                <th width="10%" class="text-center">Trạng thái</th>
                                <th width="15%" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $currentBooking = null;
                            $stt = 0;
                            foreach ($customers as $customer): 
                                $stt++;
                                $isNewBooking = ($currentBooking !== $customer['booking_code']);
                                if ($isNewBooking) $currentBooking = $customer['booking_code'];
                            ?>
                                <?php if ($isNewBooking): ?>
                                <tr class="table-info">
                                    <td colspan="9" class="fw-bold">
                                        <i class="fas fa-ticket-alt"></i> 
                                        Booking: <?= htmlspecialchars($customer['booking_code']) ?> 
                                        - Người liên hệ: <?= htmlspecialchars($customer['contact_name']) ?> 
                                        (<?= htmlspecialchars($customer['contact_phone']) ?>)
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <tr class="<?= $customer['checked_in'] ? 'table-success' : '' ?>" id="customer-row-<?= $customer['id'] ?>">
                                    <td class="text-center"><?= $stt ?></td>
                                    <td><?= htmlspecialchars($customer['booking_code']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($customer['full_name']) ?></strong>
                                        <?php if ($customer['gender']): ?>
                                            <br><small class="text-muted">
                                                <?= $customer['gender'] === 'MALE' ? '👨 Nam' : ($customer['gender'] === 'FEMALE' ? '👩 Nữ' : '⚧ Khác') ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $customer['customer_type'] === 'ADULT' ? 'bg-primary' : 'bg-info' ?>">
                                            <?= $customer['customer_type'] === 'ADULT' ? 'Người lớn' : 'Trẻ em' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($customer['phone'] ?? '-') ?></td>
                                    <td><?= $customer['date_of_birth'] ? date('d/m/Y', strtotime($customer['date_of_birth'])) : '-' ?></td>
                                    <td>
                                        <small><?= htmlspecialchars($customer['notes'] ?? '-') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($customer['checked_in']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Đã check-in
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i d/m', strtotime($customer['checked_in_at'])) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-clock"></i> Chưa check-in
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($customer['checked_in']): ?>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="undoCheckIn(<?= $customer['id'] ?>)"
                                                    title="Hủy check-in">
                                                <i class="fas fa-undo"></i> Hủy
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-success" 
                                                    onclick="checkInCustomer(<?= $customer['id'] ?>)"
                                                    title="Check-in">
                                                <i class="fas fa-check"></i> Check-in
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-3 mb-5">
        <a href="?act=assigned-tours" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách tour
        </a>
    </div>
</div>

<script>
function checkInCustomer(customerId) {
    if (!confirm('Xác nhận check-in khách này?')) return;
    
    fetch(`?act=booking-checkin&id=${customerId}`, {
        method: 'GET'
    })
    .then(response => response.text())
    .then(() => {
        location.reload();
    })
    .catch(error => {
        alert('Có lỗi xảy ra: ' + error.message);
    });
}

function undoCheckIn(customerId) {
    if (!confirm('Xác nhận hủy check-in?')) return;
    
    fetch(`?act=admin-booking-customer-undo-checkin&id=${customerId}`, {
        method: 'GET'
    })
    .then(response => response.text())
    .then(() => {
        location.reload();
    })
    .catch(error => {
        alert('Có lỗi xảy ra: ' + error.message);
    });
}

function checkInAll() {
    if (!confirm('Xác nhận check-in tất cả khách chưa check-in?')) return;
    
    const uncheckedRows = document.querySelectorAll('tr:not(.table-success) button[onclick^="checkInCustomer"]');
    
    if (uncheckedRows.length === 0) {
        alert('Tất cả khách đã được check-in!');
        return;
    }
    
    let completed = 0;
    uncheckedRows.forEach((btn) => {
        const customerId = btn.getAttribute('onclick').match(/\d+/)[0];
        
        fetch(`?act=booking-checkin&id=${customerId}`, {
            method: 'GET'
        }).then(() => {
            completed++;
            if (completed === uncheckedRows.length) {
                location.reload();
            }
        });
    });
}
</script>

<style>
.table tbody tr:hover {
    background-color: #f8f9fa;
}

.table-success {
    background-color: #d1e7dd !important;
}

.progress {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card {
    border-radius: 8px;
}

.badge {
    padding: 6px 12px;
    font-size: 0.85rem;
}
</style>