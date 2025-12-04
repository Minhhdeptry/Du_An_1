<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Booking</title>

    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        .page-title {
            font-weight: 600;
            font-size: 1.5rem;
        }

        .card {
            border-radius: 12px;
        }

        .table thead th,
        .table tbody td {
            vertical-align: middle;
            text-align: center;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
            transition: 0.2s;
        }

        .btn-sm {
            min-width: 60px;
        }

        .search-form .form-control {
            min-width: 250px;
        }
    </style>
</head>

<body class="bg-light">

<?php
// Map trạng thái sang tiếng Việt
$statusText = [
    'PENDING'   => 'Chờ xử lý',
    'CONFIRMED' => 'Đã xác nhận',
    'PAID'      => 'Đã thanh toán',
    'COMPLETED' => 'Hoàn thành',
    'CANCELED'  => 'Đã hủy',
];

// Map màu badge
$statusClass = [
    'PENDING'   => 'bg-warning text-dark',
    'CONFIRMED' => 'bg-info text-dark',
    'PAID'      => 'bg-success',
    'COMPLETED' => 'bg-primary',
    'CANCELED'  => 'bg-danger',
];
?>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title">📋 Danh sách Booking</h1>
        <a href="index.php?act=admin-booking-create" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Tạo Booking
        </a>
    </div>

    <!-- Form tìm kiếm -->
    <form class="row g-2 mb-4 search-form" method="get" action="index.php">
        <input type="hidden" name="act" value="admin-booking">
        <div class="col-auto">
            <input type="text" name="keyword" class="form-control"
                   placeholder="Tìm theo mã booking / khách / tour..."
                   value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Tìm kiếm</button>
        </div>
        <?php if (!empty($_GET['keyword'])): ?>
            <div class="col-auto">
                <a href="index.php?act=admin-booking" class="btn btn-secondary">Xóa</a>
            </div>
        <?php endif; ?>
    </form>

    <!-- Bảng Booking -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>STT</th>
                            <th>Mã Booking</th>
                            <th>Khách hàng</th>
                            <th>SĐT</th>
                            <th>Tour</th>
                            <th>Người lớn</th>
                            <th>Trẻ em</th>
                            <th>Tổng người</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày khởi hành</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bookings)): ?>
                            <?php foreach ($bookings as $i => $bk): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><b><?= htmlspecialchars($bk['booking_code']) ?></b></td>
                                    <td class="text-start"><?= htmlspecialchars($bk['contact_name']) ?></td>
                                    <td><?= htmlspecialchars($bk['contact_phone']) ?></td>
                                    <td class="text-start"><?= htmlspecialchars($bk['tour_name']) ?></td>
                                    <td><?= $bk['adults'] ?></td>
                                    <td><?= $bk['children'] ?></td>
                                    <td><?= $bk['total_people'] ?></td>
                                    <td><?= number_format($bk['total_amount'], 0, ',', '.') ?> đ</td>
                                    <td>
                                        <span class="badge <?= $statusClass[$bk['status']] ?? 'bg-secondary' ?>">
                                            <?= $statusText[$bk['status']] ?? $bk['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= $bk['depart_date'] ?></td>
                                    <td><?= $bk['created_at'] ?></td>
                                    <td>
                                        <a href="index.php?act=admin-booking-edit&id=<?= $bk['id'] ?>"
                                           class="btn btn-sm btn-warning mb-1" title="Sửa Booking">
                                            <i class="bi bi-pencil-square"></i> Sửa
                                        </a>
                                        <a href="index.php?act=admin-booking-delete&id=<?= $bk['id'] ?>"
                                           onclick="return confirm('Xóa booking này?')"
                                           class="btn btn-sm btn-danger mb-1" title="Xóa Booking">
                                            <i class="bi bi-trash"></i> Xóa
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="13" class="text-center py-3">Không có booking nào</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
