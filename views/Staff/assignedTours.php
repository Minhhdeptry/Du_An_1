<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tours Được Phân Công</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f4f6f9;
        }

        .container-box {
            margin-top: 30px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        table img {
            width: 70px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }

        .table thead {
            background: #28a745;
            color: white;
        }

        .btn-action {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
    </style>
</head>

<body>
    <div class="container container-box">
        <h2 class="text-success mb-3">Tours Được Phân Công</h2>

        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên Tour</th>
                    <th>Ngày Bắt Đầu</th>
                    <th>Ngày Kết Thúc</th>
                    <th>Số Khách</th>
                    <th>Hình</th>
                    <th>Điểm Tập Trung</th>
                    <th>Điều Hành Phụ Trách</th>
                    <th>Phương Tiện</th>
                    <th>Trạng Thái</th>
                    <th style="width:160px;">Hành Động</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($assignedTours)) : ?>
                    <?php foreach ($assignedTours as $idx => $tour) : ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><?= htmlspecialchars($tour['tour_title']) ?></td>
                            <td><?= date('d/m/Y', strtotime($tour['depart_date'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($tour['return_date'])) ?></td>
                            <td><?= $tour['seats_total'] ?> khách</td>
                            <td><img src="<?= !empty($tour['image']) ? $tour['image'] : 'https://via.placeholder.com/70x50?text=No+Image' ?>" /></td>
                            <td><?= htmlspecialchars($tour['note']) ?></td>
                            <td><?= htmlspecialchars($tour['guide_name']) ?></td>
                            <td><?= htmlspecialchars($tour['vehicle'] ?? '') ?></td>
                            <td><span class="badge bg-<?= $tour['status'] === 'OPEN' ? 'success' : 'secondary' ?>"><?= $tour['status'] ?></span></td>
                            <td class="btn-action">
                                <a href="index.php?act=admin-tour-detail&id=<?= $tour['tour_id'] ?>" class="btn btn-primary btn-sm">Xem chi tiết</a>
                                <a href="#" class="btn btn-success btn-sm">Check-in khách</a>
                                <a href="#" class="btn btn-secondary btn-sm">Báo cáo tour</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="11" class="text-center">Không có tour nào được phân công.</td>
                    </tr>
                <?php endif; ?>
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="11" class="text-start">
                        <a href="index.php?act=home" class="btn btn-danger">Quay Lại</a>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>

</html>