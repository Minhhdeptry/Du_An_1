<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Báo cáo tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-4">📊 Báo cáo tour</h1>

        <form class="row g-2 mb-4" method="get" action="index.php">
            <input type="hidden" name="act" value="admin-report">

            <label for="year" class="col-auto col-form-label">Chọn năm:</label>
            <div class="col-auto">
                <input type="number" name="year" id="year" class="form-control"
                    value="<?php echo isset($_GET['year']) ? intval($_GET['year']) : date('Y'); ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Xem</button>
            </div>
        </form>


        <div class="row mb-5">
            <div class="col-12 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Doanh thu tour mỗi tháng</div>
                    <div class="card-body">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">Tổng khách theo tour mỗi tháng</div>
                    <div class="card-body">
                        <canvas id="customerChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Chuẩn bị datasets cho Chart.js
    
    $colors = ['rgba(40,167,69,0.7)', 'rgba(54,162,235,0.7)', 'rgba(255,99,132,0.7)', 'rgba(255,206,86,0.7)', 'rgba(153,102,255,0.7)', 'rgba(255,159,64,0.7)'];

    // Datasets doanh thu
    $revenueDatasets = [];
    $i = 0;
    foreach ($revenueData as $tour => $monthsData) {
        $revenueDatasets[] = [
            'label' => $tour,
            'data' => array_values($monthsData), // 12 tháng
            'borderColor' => str_replace('0.7', '1', $colors[$i % count($colors)]),
            'backgroundColor' => $colors[$i % count($colors)],
            'fill' => false,
            'tension' => 0.3
        ];
        $i++;
    }

    // Datasets tổng khách
    $customerDatasets = [];
    $i = 0;
    foreach ($customerData as $tour => $monthsData) {
        $customerDatasets[] = [
            'label' => $tour,
            'data' => array_values($monthsData), // 12 tháng
            'borderColor' => str_replace('0.7', '1', $colors[$i % count($colors)]),
            'backgroundColor' => $colors[$i % count($colors)],
            'fill' => false,
            'tension' => 0.3
        ];
        $i++;
    }
    ?>

    <script>
        const months = <?php echo json_encode(range(1, 12)); ?>;

        // Doanh thu chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: <?php echo json_encode($revenueDatasets); ?>
            },
            options: {
                responsive: true,
                plugins: { title: { display: true, text: 'Doanh thu tour mỗi tháng' } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Tổng khách chart
        const customerCtx = document.getElementById('customerChart').getContext('2d');
        new Chart(customerCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: <?php echo json_encode($customerDatasets); ?>
            },
            options: {
                responsive: true,
                plugins: { title: { display: true, text: 'Tổng khách theo tour mỗi tháng' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>

</html>