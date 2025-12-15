<?php
require_once "./commons/function.php";
$pdo = connectDB();

$userId = $_SESSION['user']['id'];

$stmtStaff = $pdo->prepare("SELECT s.*, u.full_name, u.email FROM staffs s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
$stmtStaff->execute([$userId]);
$staffInfo = $stmtStaff->fetch(PDO::FETCH_ASSOC);

if (!$staffInfo) {
    echo '<div class="alert alert-danger">Không tìm thấy thông tin HDV!</div>';
    return;
}

$staffId = $staffInfo['id'];

$stmtStats = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT sth.tour_schedule_id) as total_tours,
        COUNT(DISTINCT CASE WHEN sth.status = 'COMPLETED' THEN sth.tour_schedule_id END) as completed_tours,
        COUNT(DISTINCT CASE WHEN sth.status = 'SCHEDULED' AND ts.depart_date > CURDATE() THEN sth.tour_schedule_id END) as upcoming_tours,
        COUNT(DISTINCT CASE WHEN sth.status = 'SCHEDULED' AND ts.depart_date <= CURDATE() AND ts.return_date >= CURDATE() THEN sth.tour_schedule_id END) as ongoing_tours
    FROM staff_tour_history sth
    JOIN tour_schedule ts ON sth.tour_schedule_id = ts.id
    WHERE sth.staff_id = ?
");
$stmtStats->execute([$staffId]);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

$stmtCustomers = $pdo->prepare("
    SELECT SUM(b.adults + b.children) as total_customers
    FROM staff_tour_history sth
    JOIN tour_schedule ts ON sth.tour_schedule_id = ts.id
    JOIN bookings b ON b.tour_schedule_id = ts.id
    WHERE sth.staff_id = ? AND b.status NOT IN ('CANCELED')
");
$stmtCustomers->execute([$staffId]);
$totalCustomers = $stmtCustomers->fetch(PDO::FETCH_ASSOC)['total_customers'] ?? 0;

$stmtRating = $pdo->prepare("
    SELECT 
        AVG(rating) as avg_rating,
        COUNT(*) as total_ratings
    FROM staff_ratings 
    WHERE staff_id = ?
");
$stmtRating->execute([$staffId]);
$ratingData = $stmtRating->fetch(PDO::FETCH_ASSOC);
$avgRating = round($ratingData['avg_rating'] ?? 0, 1);
$totalRatings = $ratingData['total_ratings'] ?? 0;

$stmtRecentTours = $pdo->prepare("
    SELECT ts.*, t.title as tour_name, t.code as tour_code,
           sth.role, sth.status as assignment_status
    FROM staff_tour_history sth
    JOIN tour_schedule ts ON sth.tour_schedule_id = ts.id
    JOIN tours t ON ts.tour_id = t.id
    WHERE sth.staff_id = ?
    ORDER BY ts.depart_date DESC
    LIMIT 5
");
$stmtRecentTours->execute([$staffId]);
$recentTours = $stmtRecentTours->fetchAll(PDO::FETCH_ASSOC);

$stmtMonthly = $pdo->prepare("
    SELECT 
        DATE_FORMAT(ts.depart_date, '%Y-%m') as month,
        COUNT(DISTINCT sth.tour_schedule_id) as tour_count,
        SUM(b.adults + b.children) as customer_count
    FROM staff_tour_history sth
    JOIN tour_schedule ts ON sth.tour_schedule_id = ts.id
    LEFT JOIN bookings b ON b.tour_schedule_id = ts.id AND b.status NOT IN ('CANCELED')
    WHERE sth.staff_id = ?
    AND ts.depart_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month DESC
");
$stmtMonthly->execute([$staffId]);
$monthlyData = $stmtMonthly->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-chart-line"></i> Thống Kê Cá Nhân
            </h2>
            <p class="text-muted mb-0">Dashboard cho HDV: <strong><?= htmlspecialchars($staffInfo['full_name']) ?></strong></p>
        </div>
        <div>
            <a href="?act=assigned-tours" class="btn btn-outline-primary">
                <i class="fas fa-bus"></i> Danh sách tour
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if (!empty($staffInfo['profile_image'])): ?>
                            <img src="<?= $staffInfo['profile_image'] ?>" 
                                 alt="Profile" 
                                 class="rounded-circle" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                 style="width: 120px; height: 120px; font-size: 48px;">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h4 class="mb-1"><?= htmlspecialchars($staffInfo['full_name']) ?></h4>
                    <p class="text-muted mb-2"><?= htmlspecialchars($staffInfo['email']) ?></p>
                    <span class="badge bg-primary mb-3">
                        <?= match($staffInfo['staff_type']) {
                            'DOMESTIC' => 'HDV Trong Nước',
                            'INTERNATIONAL' => 'HDV Quốc Tế',
                            'SPECIALIZED' => 'HDV Chuyên Biệt',
                            'GROUP_TOUR' => 'HDV Đoàn Thể',
                            default => 'HDV'
                        } ?>
                    </span>
                    
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <div class="text-warning me-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="<?= $i <= $avgRating ? 'fas' : 'far' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="fw-bold"><?= $avgRating ?>/5.0</span>
                    </div>
                    <small class="text-muted">(<?= $totalRatings ?> đánh giá)</small>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="row g-5 gap-4">
                <div class="col-md-6">
                    <div class="card text-white bg-primary shadow-sm h-100 m-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1">Tổng tour</h6>
                                    <h2 class="mb-0"><?= $stats['total_tours'] ?? 0 ?></h2>
                                </div>
                                <i class="fas fa-route fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card text-white bg-success shadow-sm h-100 m-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1">Hoàn thành</h6>
                                    <h2 class="mb-0"><?= $stats['completed_tours'] ?? 0 ?></h2>
                                </div>
                                <i class="fas fa-check-circle fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card text-white bg-warning shadow-sm h-100 m-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1">Sắp tới</h6>
                                    <h2 class="mb-0"><?= $stats['upcoming_tours'] ?? 0 ?></h2>
                                </div>
                                <i class="fas fa-clock fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card text-white bg-info shadow-sm h-100 m-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1">Khách phục vụ</h6>
                                    <h2 class="mb-0"><?= number_format($totalCustomers) ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 align-items-stretch">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Hoạt động 6 tháng gần đây
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-user-tag"></i> Vai trò trong tour
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $stmtRole = $pdo->prepare("
                        SELECT role, COUNT(*) as count
                        FROM staff_tour_history
                        WHERE staff_id = ?
                        GROUP BY role
                    ");
                    $stmtRole->execute([$staffId]);
                    $roleData = $stmtRole->fetchAll(PDO::FETCH_ASSOC);
                    
                    $guideCount = 0;
                    $assistantCount = 0;
                    foreach ($roleData as $r) {
                        if ($r['role'] === 'GUIDE') $guideCount = $r['count'];
                        if ($r['role'] === 'ASSISTANT') $assistantCount = $r['count'];
                    }
                    $totalRoles = $guideCount + $assistantCount;
                    ?>
                    
                    <canvas id="roleChart"></canvas>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-circle text-primary"></i> HDV Chính</span>
                            <strong><?= $guideCount ?> tour</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-circle text-info"></i> HDV Phụ</span>
                            <strong><?= $assistantCount ?> tour</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-history"></i> Tour gần đây
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recentTours)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-3x mb-2"></i>
                    <p>Chưa có tour nào</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tour</th>
                                <th class="text-center">Mã</th>
                                <th class="text-center">Ngày khởi hành</th>
                                <th class="text-center">Vai trò</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTours as $tour): ?>
                            <tr>
                                <td><?= htmlspecialchars($tour['tour_name']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= htmlspecialchars($tour['tour_code']) ?></span>
                                </td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($tour['depart_date'])) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $tour['role'] === 'GUIDE' ? 'bg-primary' : 'bg-info' ?>">
                                        <?= $tour['role'] === 'GUIDE' ? 'HDV Chính' : 'HDV Phụ' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $statusBadge = match($tour['status']) {
                                        'OPEN' => '<span class="badge bg-success">Mở</span>',
                                        'CLOSED' => '<span class="badge bg-warning">Đóng</span>',
                                        'FINISHED' => '<span class="badge bg-secondary">Hoàn thành</span>',
                                        'CANCELED' => '<span class="badge bg-danger">Đã hủy</span>',
                                        default => '<span class="badge bg-light text-dark">Không xác định</span>'
                                    };
                                    echo $statusBadge;
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a href="?act=tour-detail&tour_schedule_id=<?= $tour['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyData = <?= json_encode(array_reverse($monthlyData)) ?>;

new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: monthlyData.map(d => {
            const [year, month] = d.month.split('-');
            return `${month}/${year}`;
        }),
        datasets: [{
            label: 'Số tour',
            data: monthlyData.map(d => d.tour_count),
            backgroundColor: 'rgba(13, 110, 253, 0.8)',
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 1
        }, {
            label: 'Số khách',
            data: monthlyData.map(d => d.customer_count || 0),
            backgroundColor: 'rgba(25, 135, 84, 0.8)',
            borderColor: 'rgba(25, 135, 84, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                position: 'top'
            }
        }
    }
});

const roleCtx = document.getElementById('roleChart').getContext('2d');
new Chart(roleCtx, {
    type: 'doughnut',
    data: {
        labels: ['HDV Chính', 'HDV Phụ'],
        datasets: [{
            data: [<?= $guideCount ?>, <?= $assistantCount ?>],
            backgroundColor: [
                'rgba(13, 110, 253, 0.8)',
                'rgba(13, 202, 240, 0.8)'
            ],
            borderColor: [
                'rgba(13, 110, 253, 1)',
                'rgba(13, 202, 240, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<style>
.card {
    border-radius: 8px;
}

.opacity-50 {
    opacity: 0.5;
}

#monthlyChart {
    height: 300px;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}
</style>