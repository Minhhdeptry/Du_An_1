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
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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
                <!-- TOUR 1 -->
                <tr>
                    <td>1</td>
                    <td>Hà Nội – Hạ Long (2N1Đ)</td>
                    <td>15/12/2025</td>
                    <td>17/12/2025</td>
                    <td>25 khách</td>
                    <td><img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600" /></td>
                    <td>Cổng B – Bến Xe Mỹ Đình</td>
                    <td>Nguyễn Văn Hùng</td>
                    <td>Xe Khách 45 chỗ</td>
                    <td><span class="badge bg-warning">Sắp diễn ra</span></td>
                    <td class="btn-action">
                        <a href="#" class="btn btn-primary btn-sm">Xem chi tiết</a>
                        <a href="#" class="btn btn-success btn-sm">Check-in khách</a>
                        <a href="#" class="btn btn-secondary btn-sm">Báo cáo tour</a>
                    </td>
                </tr>

                <!-- TOUR 2 -->
                <tr>
                    <td>2</td>
                    <td>Du Lịch Hàn Quốc (7N6Đ)</td>
                    <td>20/12/2025</td>
                    <td>27/12/2025</td>
                    <td>32 khách</td>
                    <td><img src="https://images.unsplash.com/photo-1504893524553-b855bce32c67?w=600" /></td>
                    <td>Sảnh quốc tế – Sân bay Nội Bài</td>
                    <td>Trần Bảo Nhi</td>
                    <td>Máy bay Vietnam Airlines</td>
                    <td><span class="badge bg-primary">Đang diễn ra</span></td>
                    <td class="btn-action">
                        <a href="#" class="btn btn-primary btn-sm">Xem chi tiết</a>
                        <a href="#" class="btn btn-success btn-sm">Check-in khách</a>
                        <a href="#" class="btn btn-secondary btn-sm">Báo cáo tour</a>
                    </td>
                </tr>

                <!-- TOUR 3 -->
                <tr>
                    <td>3</td>
                    <td>Tour Sapa 3N2Đ</td>
                    <td>02/12/2025</td>
                    <td>05/12/2025</td>
                    <td>18 khách</td>
                    <td><img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=600" /></td>
                    <td>Nhà Hát Lớn Hà Nội</td>
                    <td>Phạm Thu Thảo</td>
                    <td>Xe Limousine 9 chỗ</td>
                    <td><span class="badge bg-success">Đã hoàn thành</span></td>
                    <td class="btn-action">
                        <a href="#" class="btn btn-primary btn-sm">Xem chi tiết</a>
                        <a class="btn btn-success btn-sm disabled">Check-in</a>
                        <a href="#" class="btn btn-secondary btn-sm">Báo cáo tour</a>
                    </td>
                </tr>
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
