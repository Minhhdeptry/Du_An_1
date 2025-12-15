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
            <input type="hidden" name="module" value="report">
            <input type="hidden" name="act" value="admin-report">

            <label class="col-auto col-form-label">Chọn năm:</label>
            <div class="col-auto">
                <input type="number" name="year" class="form-control"
                       value="<?= isset($_GET['year']) ? intval($_GET['year']) : date('Y') ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary">Xem</button>
            </div>
        </form>

        <!-- Biểu đồ -->
        <div class="row mb-5">
            <!-- Doanh thu -->
            <div class="col-12 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Doanh thu tour mỗi tháng</div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="120"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tổng khách -->
            <div class="col-12 col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">Tổng khách theo tháng</div>
                    <div class="card-body">
                        <canvas id="customerChart" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php 
    // Chuẩn bị dữ liệu PHP -> JS

    // dataset doanh thu (12 tháng)
    $revenueMonths = array_fill(1, 12, 0);
    foreach ($revenueByMonth as $row) {
        $revenueMonths[intval($row["month"])] = floatval($row["total_revenue"]);
    }

    // dataset tổng khách (12 tháng)
    $customerMonths = array_fill(1, 12, 0);
    foreach ($customerByMonth as $row) {
        $customerMonths[intval($row["month"])] = intval($row["total_customers"]);
    }
    ?>

    <script>
        const labels = [1,2,3,4,5,6,7,8,9,10,11,12];

        const revenueData = <?= json_encode(array_values($revenueMonths)) ?>;
        const customerData = <?= json_encode(array_values($customerMonths)) ?>;

        // --- BIỂU ĐỒ DOANH THU ---
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: "Doanh thu",
                    data: revenueData,
                    borderColor: 'rgba(54,162,235,1)',
                    backgroundColor: 'rgba(54,162,235,0.4)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { title: { display: true, text: 'Doanh thu tour mỗi tháng' } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // --- BIỂU ĐỒ KHÁCH ---
        new Chart(document.getElementById('customerChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: "Số khách",
                    data: customerData,
                    borderColor: 'rgba(40,167,69,1)',
                    backgroundColor: 'rgba(40,167,69,0.4)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { title: { display: true, text: 'Tổng khách theo tháng' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>


</body>
</html>