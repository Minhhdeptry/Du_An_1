<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-stat {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            transition: transform 0.2s;
        }

        .card-stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .page-title {
            font-weight: 600;
            font-size: 1.75rem;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .card-header {
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container mt-4">

        <h1 class="page-title mb-4">📊 Dashboard</h1>

        <!-- Section 1: KPI Cards -->
        <div class="row g-3 mb-4">
            <?php $kpiItems = [
                ['label'=>'Tổng tour','value'=>$kpi['totalTours'],'color'=>'bg-primary text-white'],
                ['label'=>'Tổng booking','value'=>$kpi['totalBookings'],'color'=>'bg-success text-white'],
                ['label'=>'Tổng khách hàng','value'=>$kpi['totalCustomers'],'color'=>'bg-warning text-dark'],
                ['label'=>'Tổng HDV','value'=>$kpi['totalGuides'],'color'=>'bg-info text-white'],
                ['label'=>'Doanh thu tháng','value'=>number_format($kpi['revenueThisMonth']).' đ','color'=>'bg-danger text-white'],
                ['label'=>'Tour hôm nay','value'=>$kpi['toursToday'],'color'=>'bg-secondary text-white']
            ]; ?>
            <?php foreach($kpiItems as $item): ?>
                <div class="col-md-2">
                    <div class="card card-stat shadow <?= $item['color'] ?>">
                        <h6><?= $item['label'] ?></h6>
                        <h3><?= $item['value'] ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Section 2: Charts -->
        <div class="row mb-4">
            <div class="col-lg-8 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Doanh thu 12 tháng</div>
                    <div class="card-body">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">Tỷ lệ trạng thái booking</div>
                    <div class="card-body">
                        <canvas id="bookingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Mini tables -->
        <div class="row mb-4">
            <div class="col-lg-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">Booking mới nhất</div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã</th>
                                    <th>Khách</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tables['latestBookings'] as $b): ?>
                                    <tr>
                                        <td><?= $b['booking_code'] ?></td>
                                        <td><?= $b['customer'] ?></td>
                                        <td><?= $b['status'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">Tour sắp khởi hành</div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tour</th>
                                    <th>Ngày đi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tables['upcomingTours'] as $t): ?>
                                    <tr>
                                        <td><?= $t['title'] ?></td>
                                        <td><?= $t['depart_date'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">Khách hàng mới</div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tables['latestCustomers'] as $c): ?>
                                    <tr>
                                        <td><?= $c['full_name'] ?></td>
                                        <td><?= $c['email'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        const revenueData = <?= json_encode(array_values($charts['revenue12Months'])) ?>;
        const bookingStatusData = <?= json_encode(array_values($charts['bookingStatus'])) ?>;

        // Line chart
        new Chart(document.getElementById('revenueChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Th1','Th2','Th3','Th4','Th5','Th6','Th7','Th8','Th9','Th10','Th11','Th12'],
                datasets: [{ label: 'Doanh thu', data: revenueData, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.2)', fill: true, tension: 0.3 }]
            }
        });

        // Pie chart
        new Chart(document.getElementById('bookingChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: ['Chờ xử lý','Đã xác nhận','Đã thanh toán','Đã hủy'],
                datasets: [{ data: bookingStatusData, backgroundColor: ['#ffc107','#0d6efd','#198754','#dc3545'] }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    </script>
</body>

</html>
