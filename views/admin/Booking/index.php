<!-- BOOTSTRAP -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title">Danh sách Booking</h1>
        
    </div>

    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Mã Booking</th>
                <th>Khách hàng</th>
                <th>SĐT</th>
                <th>Tour</th>
                <th>Số người lớn</th>
                <th>Số trẻ em</th>
                <th>Tổng sô người</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày khởi hành</th>
                <th>Ngày tạo</th>
                <th width="140">Hành động</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($bookings as $i => $bk): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><b><?= $bk['booking_code'] ?></b></td>
                    <td><?= $bk['contact_name'] ?></td>
                    <td><?= $bk['contact_phone'] ?></td>
                    <td><?= $bk['tour_name'] ?></td>
                    <td><?= $bk['adults'] ?></td>
                    <td><?= $bk['children'] ?></td>
                    <td><?= $bk['total_people'] ?></td>
                    <td><?= number_format($bk['total_amount']) ?>đ</td>
                    <td>
                        <?php if ($bk['status'] == 'PENDING'): ?>
                            <span class="badge badge-warning">Chờ xử lý</span>

                        <?php elseif ($bk['status'] == 'CONFIRMED'): ?>
                            <span class="badge badge-info">Đã xác nhận</span>

                        <?php elseif ($bk['status'] == 'PAID'): ?>
                            <span class="badge badge-success">Đã thanh toán</span>

                        <?php elseif ($bk['status'] == 'COMPLETED'): ?>
                            <span class="badge badge-primary">Hoàn thành</span>

                        <?php elseif ($bk['status'] == 'CANCELED'): ?>
                            <span class="badge badge-danger">Đã hủy</span>
                        <?php endif; ?>
                    </td>

                    <td><?= $bk['depart_date'] ?></td>

                    <td><?= $bk['created_at'] ?></td>

                    <td>
                        <a href="index.php?act=admin-booking-edit&id=<?= $bk['id'] ?>" class="btn btn-sm btn-warning">
                            Sửa
                        </a>

                        <a onclick="return confirm('Xóa booking này?')" 
                           href="index.php?act=admin-booking-delete&id=<?= $bk['id'] ?>" 
                           class="btn btn-sm btn-danger">
                            Xóa
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
</div>
