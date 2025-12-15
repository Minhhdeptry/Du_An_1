<?php
// views/admin/BookingCustomer/index.php
?>

<style>
.info-box {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.stat-card {
    text-align: center;
    padding: 15px;
    border-radius: 8px;
    border: 2px solid #e9ecef;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
}

.customer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}
</style>

<div class="container-fluid mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-people"></i> 
                Danh sách khách - <span class="text-primary"><?= htmlspecialchars($booking['booking_code']) ?></span>
            </h2>
            <p class="text-muted mb-0">
                <i class="bi bi-map"></i> <?= htmlspecialchars($booking['tour_name']) ?>
                <span class="mx-2">|</span>
                <i class="bi bi-calendar"></i> <?= date('d/m/Y', strtotime($booking['depart_date'])) ?>
            </p>
        </div>
        <div>
            <a href="index.php?act=admin-booking-customer-create&booking_id=<?= $booking['id'] ?>" 
               class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Thêm khách
            </a>
            <a href="index.php?act=admin-booking" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card <?= $validation['valid'] ? 'border-success' : 'border-danger' ?>">
                <div class="stat-value text-primary"><?= count($customers) ?></div>
                <div class="text-muted">Tổng khách đã nhập</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value text-success"><?= $counts['ADULT'] ?> / <?= $booking['adults'] ?></div>
                <div class="text-muted">Người lớn</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value text-warning"><?= $counts['CHILD'] + $counts['INFANT'] ?> / <?= $booking['children'] ?></div>
                <div class="text-muted">Trẻ em</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value text-info"><?= count(array_filter($customers, fn($c) => $c['checked_in'])) ?> / <?= count($customers) ?></div>
                <div class="text-muted">Đã check-in</div>
            </div>
        </div>
    </div>

    <!-- Validation Warning -->
    <?php if (!$validation['valid']): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Cảnh báo:</strong> <?= $validation['message'] ?>
        </div>
    <?php endif; ?>

    <!-- Booking Info -->
    <div class="info-box mb-4">
        <h5 class="mb-3"><i class="bi bi-info-circle"></i> Thông tin Booking</h5>
        <div class="row">
            <div class="col-md-4">
                <strong>Người đặt:</strong> <?= htmlspecialchars($booking['contact_name']) ?>
            </div>
            <div class="col-md-4">
                <strong>SĐT:</strong> <?= htmlspecialchars($booking['contact_phone']) ?>
            </div>
            <div class="col-md-4">
                <strong>Trạng thái:</strong> 
                <span class="badge bg-<?= $booking['status'] === 'CONFIRMED' ? 'success' : 'warning' ?>">
                    <?= $booking['status'] ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Customer List -->
    <?php if (empty($customers)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">Chưa có khách nào</h4>
                <p class="text-muted">Hãy thêm khách cho booking này</p>
                <a href="index.php?act=admin-booking-customer-create&booking_id=<?= $booking['id'] ?>" 
                   class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> Thêm khách đầu tiên
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">#</th>
                            <th width="80">Loại</th>
                            <th width="200">Họ tên</th>
                            <th width="100">Ngày sinh</th>
                            <th width="120">CMND/Passport</th>
                            <th width="120">SĐT</th>
                            <th width="180">Email</th>
                            <th width="80">Giới tính</th>
                            <th width="100">Quốc tịch</th>
                            <th width="100" class="text-center">Check-in</th>
                            <th width="180" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $i => $c): ?>
                            <tr class="<?= $c['checked_in'] ? 'table-success' : '' ?>">
                                <td><?= $i + 1 ?></td>
                                
                                <td>
                                    <?php
                                    $typeLabels = [
                                        'ADULT' => ['text' => 'Người lớn', 'class' => 'bg-primary'],
                                        'CHILD' => ['text' => 'Trẻ em', 'class' => 'bg-warning'],
                                        'INFANT' => ['text' => 'Em bé', 'class' => 'bg-info']
                                    ];
                                    $type = $typeLabels[$c['customer_type']];
                                    ?>
                                    <span class="badge <?= $type['class'] ?>"><?= $type['text'] ?></span>
                                </td>
                                
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="customer-avatar">
                                            <?= strtoupper(substr($c['full_name'], 0, 1)) ?>
                                        </div>
                                        <strong><?= htmlspecialchars($c['full_name']) ?></strong>
                                    </div>
                                </td>
                                
                                <td>
                                    <?= $c['date_of_birth'] ? date('d/m/Y', strtotime($c['date_of_birth'])) : '-' ?>
                                </td>
                                
                                <td><?= htmlspecialchars($c['id_number'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($c['email'] ?? '-') ?></td>
                                
                                <td>
                                    <?php
                                    $genders = ['MALE' => '👨 Nam', 'FEMALE' => '👩 Nữ', 'OTHER' => '👤 Khác'];
                                    echo $genders[$c['gender'] ?? 'OTHER'] ?? '-';
                                    ?>
                                </td>
                                
                                <td><?= htmlspecialchars($c['nationality'] ?? 'Việt Nam') ?></td>
                                
                                <td class="text-center">
                                    <?php if ($c['checked_in']): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Đã check-in
                                        </span>
                                        <?php if ($c['checked_in_at']): ?>
                                            <br><small class="text-muted">
                                                <?= date('H:i d/m', strtotime($c['checked_in_at'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Chưa check-in</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <?php if (!$c['checked_in']): ?>
                                            <a href="index.php?act=admin-booking-customer-checkin&id=<?= $c['id'] ?>" 
                                               class="btn btn-success" title="Check-in"
                                               onclick="return confirm('✅ Xác nhận check-in cho <?= htmlspecialchars($c['full_name']) ?>?')">
                                                <i class="bi bi-check-circle"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?act=admin-booking-customer-undo-checkin&id=<?= $c['id'] ?>" 
                                               class="btn btn-warning" title="Hủy check-in"
                                               onclick="return confirm('❌ Hủy check-in cho <?= htmlspecialchars($c['full_name']) ?>?')">
                                                <i class="bi bi-x-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="index.php?act=admin-booking-customer-edit&id=<?= $c['id'] ?>" 
                                           class="btn btn-secondary" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <a href="index.php?act=admin-booking-customer-delete&id=<?= $c['id'] ?>" 
                                           class="btn btn-danger" title="Xóa"
                                           onclick="return confirm('⚠️ Xóa khách này?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            
                            <?php if (!empty($c['notes'])): ?>
                                <tr>
                                    <td colspan="11" class="bg-light">
                                        <small>
                                            <i class="bi bi-info-circle text-primary"></i>
                                            <strong>Ghi chú:</strong> <?= nl2br(htmlspecialchars($c['notes'])) ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">