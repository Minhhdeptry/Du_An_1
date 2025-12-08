<!-- views/admin/Booking/index.php -->

<!-- THÔNG BÁO -->
<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.05);
        transition: background-color 0.2s;
    }

    .table thead {
        background: #1f2937;
        color: #fff;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .search-box {
        height: 42px;
        border-radius: 8px;
    }

    .btn-search {
        height: 42px;
        border-radius: 8px;
    }

    .badge {
        font-size: 0.85rem;
        font-weight: 500;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
    }

    .btn-sm {
        padding: 0.35rem 0.7rem;
        font-size: 0.875rem;
        border-radius: 6px;
    }

    .empty-state {
        padding: 3rem;
        text-align: center;
        background: #f8f9fa;
        border-radius: 12px;
    }

    .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
    }
</style>

<div class="container mt-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">📘 Quản lý Booking</h2>
            <p class="text-muted mb-0">
                <small>Tổng: <strong><?= count($bookings) ?></strong> booking</small>
            </p>
        </div>
        <a href="index.php?act=admin-booking-create" class="btn btn-success">
            <i class="bi bi-plus-circle me-2"></i>Tạo Booking
        </a>
    </div>

    <!-- TÌM KIẾM & FILTER -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="act" value="admin-booking">

                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="keyword" class="form-control search-box border-start-0"
                            placeholder="Tìm theo mã booking, tên khách, tên tour..."
                            value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-search w-100">
                        <i class="bi bi-search me-2"></i>Tìm kiếm
                    </button>
                </div>

                <div class="col-md-2">
                    <?php if (!empty($_GET['keyword'])): ?>
                        <a href="index.php?act=admin-booking" class="btn btn-secondary w-100">
                            <i class="bi bi-x-circle me-2"></i>Xóa bộ lọc
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline-secondary w-100" disabled>
                            <i class="bi bi-funnel"></i> Bộ lọc
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLE -->
    <?php if (empty($bookings)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h4 class="mt-3 text-muted">Không có booking nào</h4>
            <p class="text-muted">
                <?php if (!empty($_GET['keyword'])): ?>
                    Không tìm thấy kết quả cho "<strong><?= htmlspecialchars($_GET['keyword']) ?></strong>"
                <?php else: ?>
                    Chưa có booking nào trong hệ thống
                <?php endif; ?>
            </p>
            <a href="index.php?act=admin-booking-create" class="btn btn-primary mt-2">
                <i class="bi bi-plus-circle me-2"></i>Tạo booking đầu tiên
            </a>
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="50">STT</th>
                            <th width="120">Mã Booking</th>
                            <th>Khách hàng</th>
                            <th>Tour</th>
                            <th width="100">Khởi hành</th>
                            <th width="70" class="text-center">Người</th>
                            <th width="120" class="text-end">Tổng tiền</th>
                            <th width="130" class="text-center">Trạng thái Booking</th>
                            <th width="140" class="text-center">Trạng thái Thanh toán</th>
                            <th width="200" class="text-center">Hành động</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $i = 1; foreach ($bookings as $b): ?>
                            <tr>
                                <td><?= $i++ ?></td>

                                <td>
                                    <code class="bg-light px-2 py-1 rounded">
                                        <?= htmlspecialchars($b['booking_code']) ?>
                                    </code>
                                </td>

                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($b['contact_name']) ?></strong>
                                        <?php if (!empty($b['contact_phone'])): ?>
                                            <br><small class="text-muted">
                                                <i class="bi bi-telephone"></i>
                                                <?= htmlspecialchars($b['contact_phone']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="text-truncate" style="max-width: 200px;"
                                        title="<?= htmlspecialchars($b['tour_name']) ?>">
                                        <?= htmlspecialchars($b['tour_name']) ?>
                                    </div>
                                </td>

                                <td>
                                    <small><?= date('d/m/Y', strtotime($b['depart_date'])) ?></small>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-secondary">
                                        <?= (int)$b['adults'] + (int)$b['children'] ?>
                                    </span>
                                </td>

                                <td class="text-end">
                                    <strong class="text-primary">
                                        <?= number_format((float)$b['total_amount'], 0, ',', '.') ?>đ
                                    </strong>
                                </td>

                                <!-- ✅ CỘT 1: Trạng thái BOOKING (4 trạng thái theo yêu cầu) -->
                                <td class="text-center">
                                    <?php
                                    // Logic: Dựa vào payment_status để hiển thị trạng thái booking
                                    $paymentStatus = $b['payment_status'] ?? 'PENDING';
                                    $bookingStatus = $b['status'] ?? 'PENDING';
                                    
                                    if ($bookingStatus === 'CANCELED') {
                                        // Nếu đã hủy → hiển thị Hủy
                                        $displayStatus = '<span class="badge bg-danger">❌ Hủy</span>';
                                    } elseif ($bookingStatus === 'COMPLETED') {
                                        // Nếu hoàn tất → hiển thị Hoàn tất
                                        $displayStatus = '<span class="badge bg-success">🎉 Hoàn tất</span>';
                                    } elseif ($paymentStatus === 'FULL_PAID') {
                                        // Nếu đã thanh toán đủ → hiển thị Hoàn tất (hoặc có thể để "Đã cọc" nếu chưa hoàn thành tour)
                                        $displayStatus = '<span class="badge bg-success">🎉 Hoàn tất</span>';
                                    } elseif ($paymentStatus === 'DEPOSIT_PAID') {
                                        // Nếu đã cọc → hiển thị Đã cọc
                                        $displayStatus = '<span class="badge bg-info">💵 Đã cọc</span>';
                                    } else {
                                        // Mặc định → Chờ xác nhận
                                        $displayStatus = '<span class="badge bg-warning text-dark">⏳ Chờ xác nhận</span>';
                                    }
                                    
                                    echo $displayStatus;
                                    ?>
                                </td>

                                <!-- ✅ CỘT 2: Trạng thái THANH TOÁN (từ payment_status) -->
                                <td class="text-center">
                                    <?php
                                    $paymentStatusBadge = match($b['payment_status'] ?? 'PENDING') {
                                        'FULL_PAID' => '<span class="badge bg-success">💰 Đã thanh toán đủ</span>',
                                        'DEPOSIT_PAID' => '<span class="badge bg-info">💵 Đã cọc</span>',
                                        default => '<span class="badge bg-secondary">⏸️ Chưa thanh toán</span>'
                                    };
                                    echo $paymentStatusBadge;
                                    ?>
                                </td>

                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Nút Sửa -->
                                        <a href="index.php?act=admin-booking-edit&id=<?= $b['id'] ?>" 
                                           class="btn btn-warning" title="Sửa booking">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <!-- Nút Xác nhận (nếu PENDING) -->
                                        <?php if ($b['status'] === 'PENDING'): ?>
                                            <a href="index.php?act=admin-booking-confirm&id=<?= $b['id'] ?>" 
                                               class="btn btn-success"
                                               onclick="return confirm('Xác nhận booking này?')" 
                                               title="Xác nhận">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Nút Chi tiết -->
                                        <a href="index.php?act=admin-booking-detail&id=<?= $b['id'] ?>" 
                                           class="btn btn-info" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <!-- Nút Hủy (nếu chưa hủy) -->
                                        <?php if ($b['status'] !== 'CANCELED'): ?>
                                            <a href="index.php?act=admin-booking-cancel&id=<?= $b['id'] ?>" 
                                               class="btn btn-danger"
                                               onclick="return confirm('⚠️ Bạn có chắc muốn HỦY booking này?\n\nLưu ý: Hành động này KHÔNG THỂ hoàn tác!')"
                                               title="Hủy booking">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<script>
// Auto dismiss alerts sau 5s
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>