<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .table thead {
        background: #1f2937;
        /* màu đen xám giống ảnh */
        color: #fff;
    }

    .search-box input {
        height: 42px;
    }

    .btn-search {
        height: 42px;
    }
</style>

<div class="container mt-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">
            📘 Danh sách Booking
        </h2>
    </div>

    <!-- TÌM KIẾM (giống ảnh mẫu – bên trái, gọn, nút xanh) -->
    <form method="GET" action="index.php" class="d-flex gap-2 mb-3">
        <input type="hidden" name="act" value="admin-booking">

        <input type="text" name="keyword" class="form-control search-box" style="max-width: 300px;"
            placeholder="Tìm theo mã, tên khách, tên tour" value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">

        <button class="btn btn-primary btn-search">
            🔍 Tìm kiếm
        </button>

        <?php if (!empty($_GET['keyword'])): ?>
            <!-- Nút xóa input / reset -->
            <button type="submit" class="btn btn-secondary" onclick="this.form.keyword.value='';">
                ✖ Xóa
            </button>
        <?php endif; ?>
    </form>



    <?php if (empty($bookings)): ?>
        <div class="alert alert-info">Không có booking nào phù hợp.</div>
    <?php else: ?>

        <?php
        $statusText = [
            'PENDING' => 'Chờ xử lý',
            'CONFIRMED' => 'Đã xác nhận',
            'PAID' => 'Đã thanh toán',
            'COMPLETED' => 'Hoàn thành',
            'CANCELED' => 'Đã hủy',
        ];

        $statusColor = [
            'PENDING' => 'warning',   // vàng
            'CONFIRMED' => 'primary',   // xanh dương
            'PAID' => 'info',      // xanh nhạt
            'COMPLETED' => 'success',   // xanh lá
            'CANCELED' => 'danger',    // đỏ
        ];
        ?>

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">

                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã</th>
                        <th>Khách</th>
                        <th>Tour</th>
                        <th>Khởi hành</th>
                        <th>Người</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i = 1;
                    foreach ($bookings as $b): ?>
                        <tr>
                            <td><?= $i++ ?></td>

                            <td><?= htmlspecialchars($b['booking_code']) ?></td>
                            <td><?= htmlspecialchars($b['contact_name']) ?></td>
                            <td><?= htmlspecialchars($b['tour_name']) ?></td>
                            <td><?= htmlspecialchars($b['depart_date']) ?></td>

                            <td><?= (int) $b['adults'] + (int) $b['children'] ?></td>

                            <td><?= number_format((float) $b['total_amount'], 0, ',', '.') ?></td>

                            <td>
                                <span class="badge bg-<?= $statusColor[$b['status']] ?? 'secondary' ?> px-3 py-2">
                                    <?= $statusText[$b['status']] ?? $b['status'] ?>
                                </span>
                            </td>


                            <td class="text-center">

                                <!-- Nút Sửa -->
                                <a href="index.php?act=admin-booking-edit&id=<?= $b['id'] ?>"
                                    class="btn btn-warning btn-sm me-1">
                                    ✏️ Sửa
                                </a>

                                <!-- Nút Hủy -->
                                <a href="index.php?act=admin-booking-cancel&id=<?= $b['id'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Hủy booking này?');">
                                    🗑 Xóa
                                </a>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>

    <?php endif; ?>

</div>