<?php
// ============================================
// FILE: views/admin/Staff/detail.php (COMPLETE)
// ============================================

// Lấy thêm dữ liệu cần thiết
require_once "./models/admin/StaffCertificateModel.php";
require_once "./models/admin/StaffTourHistoryModel.php";
require_once "./models/admin/StaffRatingModel.php";

$certModel = new StaffCertificateModel();
$historyModel = new StaffTourHistoryModel();
$ratingModel = new StaffRatingModel();

$certificates = $certModel->getStaffCertificates($staff['id']);
$history = $historyModel->getStaffHistory($staff['id'], 10);
$ratings = $ratingModel->getStaffRatings($staff['id'], 10);
$statistics = $ratingModel->getRatingStatistics($staff['id']);

// Tính toán thống kê
$totalTours = count($history);
$completedTours = count(array_filter($history, fn($h) => $h['status'] === 'COMPLETED'));
$totalCustomers = array_sum(array_column($history, 'total_customers'));
$avgRating = $statistics['avg_rating'] ?? 0;
?>

<style>
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 16px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 5px solid white;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        object-fit: cover;
    }

    .profile-name {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .profile-role {
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.95rem;
    }

    .info-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        width: 180px;
        font-weight: 600;
        color: #495057;
    }

    .info-value {
        flex: 1;
        color: #212529;
    }

    .badge-custom {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .tab-custom {
        border-bottom: 3px solid transparent;
        padding: 12px 24px;
        font-weight: 600;
        color: #6c757d;
        transition: all 0.3s;
    }

    .tab-custom:hover {
        color: #667eea;
    }

    .tab-custom.active {
        color: #667eea;
        border-bottom-color: #667eea;
    }

    .timeline-item {
        position: relative;
        padding-left: 30px;
        padding-bottom: 20px;
        border-left: 2px solid #e9ecef;
    }

    .timeline-item:last-child {
        border-left-color: transparent;
    }

    .timeline-dot {
        position: absolute;
        left: -6px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #667eea;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #667eea;
    }

    .cert-card {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s;
    }

    .cert-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }

    .rating-stars {
        color: #ffc107;
        font-size: 1.2rem;
    }

    .action-btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
</style>

<div class="container-fluid mt-4">

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="d-flex justify-content-between align-items-start position-relative">
            <div class="d-flex gap-4">
                <?php if (!empty($staff['profile_image'])): ?>
                    <?php
                    $image = trim($staff['profile_image']);
                    if (preg_match('#^(https?:)?//#i', $image) || str_starts_with($image, '/')) {
                        $imageSrc = $image;
                    } elseif (str_contains($image, 'assets/')) {
                        $imageSrc = $image;
                    } else {
                        $imageSrc = 'assets/images/staff/' . $image;
                    }
                    ?>
                    <img src="<?= htmlspecialchars($imageSrc) ?>" alt="Avatar" class="profile-avatar"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="profile-avatar bg-light text-primary d-none align-items-center justify-content-center"
                        style="font-size: 3rem; font-weight: 700;">
                        <?= strtoupper(substr($staff['full_name'], 0, 1)) ?>
                    </div>
                <?php else: ?>
                    <div class="profile-avatar bg-light text-primary d-flex align-items-center justify-content-center"
                        style="font-size: 3rem; font-weight: 700;">
                        <?= strtoupper(substr($staff['full_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>

                <div>
                    <h1 class="profile-name"><?= htmlspecialchars($staff['full_name']) ?></h1>
                    <p class="profile-role mb-3">
                        <?php
                        $typeLabels = [
                            'DOMESTIC' => '🏠 Hướng dẫn viên Nội địa',
                            'INTERNATIONAL' => '✈️ Hướng dẫn viên Quốc tế',
                            'SPECIALIZED' => '🎯 Hướng dẫn viên Chuyên tuyến',
                            'GROUP_TOUR' => '👥 Hướng dẫn viên Khách đoàn'
                        ];
                        echo $typeLabels[$staff['staff_type']] ?? $staff['staff_type'];
                        ?>
                    </p>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge-custom bg-white text-dark">
                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($staff['email']) ?>
                        </span>
                        <span class="badge-custom bg-white text-dark">
                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($staff['phone']) ?>
                        </span>
                        <?php if (!empty($staff['experience_years'])): ?>
                            <span class="badge-custom bg-white text-dark">
                                <i class="bi bi-award"></i> <?= $staff['experience_years'] ?> năm kinh nghiệm
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="index.php?act=admin-staff-edit&id=<?= $staff['id'] ?>" class="btn btn-light action-btn">
                    <i class="bi bi-pencil"></i> Sửa
                </a>
               
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?= $totalTours ?></div>
                <div class="stat-label">Tổng số tour</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?= $completedTours ?></div>
                <div class="stat-label">Tour hoàn thành</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?= $totalCustomers ?></div>
                <div class="stat-label">Khách đã phục vụ</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value rating-stars">⭐ <?= number_format($avgRating, 1) ?></div>
                <div class="stat-label">Đánh giá trung bình</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link tab-custom active" data-bs-toggle="tab" href="#info">
                <i class="bi bi-info-circle"></i> Thông tin
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link tab-custom" data-bs-toggle="tab" href="#certificates">
                <i class="bi bi-award"></i> Chứng chỉ
                <span class="badge bg-primary rounded-pill"><?= count($certificates) ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link tab-custom" data-bs-toggle="tab" href="#history">
                <i class="bi bi-clock-history"></i> Lịch sử tour
                <span class="badge bg-success rounded-pill"><?= count($history) ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link tab-custom" data-bs-toggle="tab" href="#ratings">
                <i class="bi bi-star"></i> Đánh giá
                <span class="badge bg-warning rounded-pill"><?= count($ratings) ?></span>
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">

        <!-- Tab 1: Thông tin -->
        <div id="info" class="tab-pane fade show active">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card">
                        <h5 class="mb-3"><i class="bi bi-person-badge"></i> Thông tin cá nhân</h5>
                        
                        <div class="info-row">
                            <div class="info-label">Họ và tên:</div>
                            <div class="info-value"><?= htmlspecialchars($staff['full_name']) ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value"><?= htmlspecialchars($staff['email']) ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Số điện thoại:</div>
                            <div class="info-value"><?= htmlspecialchars($staff['phone']) ?></div>
                        </div>

                        <?php if (!empty($staff['date_of_birth'])): ?>
                            <div class="info-row">
                                <div class="info-label">Ngày sinh:</div>
                                <div class="info-value">
                                    <?= date('d/m/Y', strtotime($staff['date_of_birth'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($staff['id_number'])): ?>
                            <div class="info-row">
                                <div class="info-label">CMND/CCCD:</div>
                                <div class="info-value"><?= htmlspecialchars($staff['id_number']) ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="info-row">
                            <div class="info-label">Trạng thái:</div>
                            <div class="info-value">
                                <span class="badge <?= $staff['status'] == 'ACTIVE' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $staff['status'] == 'ACTIVE' ? '✅ Đang làm việc' : '⏸️ Nghỉ việc' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-card">
                        <h5 class="mb-3"><i class="bi bi-briefcase"></i> Thông tin nghề nghiệp</h5>

                        <div class="info-row">
                            <div class="info-label">Phân loại:</div>
                            <div class="info-value">
                                <?php
                                $types = [
                                    'DOMESTIC' => '🏠 Nội địa',
                                    'INTERNATIONAL' => '✈️ Quốc tế',
                                    'SPECIALIZED' => '🎯 Chuyên tuyến',
                                    'GROUP_TOUR' => '👥 Khách đoàn'
                                ];
                                echo $types[$staff['staff_type']] ?? $staff['staff_type'];
                                ?>
                            </div>
                        </div>

                        <?php if (!empty($staff['qualification'])): ?>
                            <div class="info-row">
                                <div class="info-label">Trình độ:</div>
                                <div class="info-value"><?= htmlspecialchars($staff['qualification']) ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="info-row">
                            <div class="info-label">Kinh nghiệm:</div>
                            <div class="info-value">
                                <span class="badge bg-info"><?= $staff['experience_years'] ?? 0 ?> năm</span>
                            </div>
                        </div>

                        <?php if (!empty($staff['languages'])): ?>
                            <div class="info-row">
                                <div class="info-label">Ngôn ngữ:</div>
                                <div class="info-value"><?= htmlspecialchars($staff['languages']) ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="info-row">
                            <div class="info-label">Đánh giá:</div>
                            <div class="info-value">
                                <?php if (!empty($staff['rating'])): ?>
                                    <span class="rating-stars">⭐ <?= number_format($staff['rating'], 1) ?>/5</span>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Sức khỏe:</div>
                            <div class="info-value">
                                <?php
                                $health = [
                                    'good' => '💚 Tốt',
                                    'fair' => '💛 Trung bình',
                                    'poor' => '❤️ Yếu'
                                ];
                                echo $health[$staff['health_status'] ?? 'good'] ?? 'N/A';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($staff['notes'])): ?>
                <div class="info-card">
                    <h5 class="mb-3"><i class="bi bi-file-text"></i> Ghi chú</h5>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($staff['notes'])) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab 2: Chứng chỉ -->
        <div id="certificates" class="tab-pane fade">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-award"></i> Danh sách chứng chỉ (<?= count($certificates) ?>)</h5>
                <a href="index.php?act=admin-staff-cert&staff_id=<?= $staff['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Quản lý chứng chỉ
                </a>
            </div>

            <?php if (empty($certificates)): ?>
                <div class="info-card text-center py-5">
                    <i class="bi bi-award" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3 text-muted">Chưa có chứng chỉ</h4>
                    <a href="index.php?act=admin-staff-cert-create&staff_id=<?= $staff['id'] ?>"
                        class="btn btn-primary mt-2">
                        Thêm chứng chỉ đầu tiên
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($certificates as $cert): ?>
                        <div class="col-md-6 mb-3">
                            <div class="cert-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0"><?= htmlspecialchars($cert['certificate_name']) ?></h6>
                                    <?php
                                    $statusBadge = match ($cert['status']) {
                                        'VALID' => '<span class="badge bg-success">Còn hiệu lực</span>',
                                        'PENDING_RENEWAL' => '<span class="badge bg-warning">Sắp hết hạn</span>',
                                        'EXPIRED' => '<span class="badge bg-danger">Hết hạn</span>',
                                        default => ''
                                    };
                                    echo $statusBadge;
                                    ?>
                                </div>

                                <?php if (!empty($cert['certificate_number'])): ?>
                                    <p class="mb-1">
                                        <small class="text-muted">Số:</small>
                                        <strong><?= htmlspecialchars($cert['certificate_number']) ?></strong>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($cert['issuing_organization'])): ?>
                                    <p class="mb-1">
                                        <small class="text-muted">Đơn vị cấp:</small>
                                        <?= htmlspecialchars($cert['issuing_organization']) ?>
                                    </p>
                                <?php endif; ?>

                                <div class="row mt-2">
                                    <?php if (!empty($cert['issue_date'])): ?>
                                        <div class="col-6">
                                            <small class="text-muted">Ngày cấp:</small><br>
                                            <strong><?= date('d/m/Y', strtotime($cert['issue_date'])) ?></strong>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($cert['expiry_date'])): ?>
                                        <div class="col-6">
                                            <small class="text-muted">Hết hạn:</small><br>
                                            <strong><?= date('d/m/Y', strtotime($cert['expiry_date'])) ?></strong>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($cert['certificate_file'])): ?>
                                    <div class="mt-2">
                                        <a href="<?= htmlspecialchars($cert['certificate_file']) ?>" target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-file-earmark-pdf"></i> Xem file
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab 3: Lịch sử tour -->
        <div id="history" class="tab-pane fade">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Lịch sử dẫn tour</h5>
                <a href="index.php?act=admin-staff-performance&staff_id=<?= $staff['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-graph-up"></i> Xem hiệu suất đầy đủ
                </a>
            </div>

            <?php if (empty($history)): ?>
                <div class="info-card text-center py-5">
                    <i class="bi bi-journal-x" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3 text-muted">Chưa có lịch sử tour</h4>
                </div>
            <?php else: ?>
                <div class="info-card">
                    <div class="timeline">
                        <?php foreach ($history as $h): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($h['tour_code']) ?></span>
                                        <strong class="ms-2"><?= htmlspecialchars($h['tour_name']) ?></strong>
                                    </div>
                                    <span class="badge <?= $h['status'] == 'COMPLETED' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= $h['status'] ?>
                                    </span>
                                </div>

                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i>
                                    <?= date('d/m/Y', strtotime($h['depart_date'])) ?>
                                    -
                                    <?= date('d/m/Y', strtotime($h['return_date'])) ?>
                                </small>

                                <?php if ($h['total_bookings'] || $h['total_customers']): ?>
                                    <div class="mt-1">
                                        <small>
                                            <i class="bi bi-people"></i>
                                            <?= $h['total_bookings'] ?? 0 ?> booking,
                                            <?= $h['total_customers'] ?? 0 ?> khách
                                        </small>
                                    </div>
                                <?php endif; ?>

                                <?php if ($h['performance_rating']): ?>
                                    <div class="mt-1">
                                        <span class="rating-stars">
                                            ⭐ <?= number_format($h['performance_rating'], 1) ?>/5
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab 4: Đánh giá -->
        <div id="ratings" class="tab-pane fade">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-star"></i> Đánh giá từ khách hàng</h5>
                <a href="index.php?act=admin-staff-rating&staff_id=<?= $staff['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-list"></i> Xem tất cả đánh giá
                </a>
            </div>

            <?php if (empty($ratings)): ?>
                <div class="info-card text-center py-5">
                    <i class="bi bi-star" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3 text-muted">Chưa có đánh giá</h4>
                </div>
            <?php else: ?>
                <?php foreach ($ratings as $r): ?>
                    <div class="info-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">
                                    <span class="badge bg-info"><?= htmlspecialchars($r['booking_code']) ?></span>
                                    <?= htmlspecialchars($r['customer_name'] ?? 'Khách hàng') ?>
                                </h6>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?= $i <= $r['rating'] ? '⭐' : '☆' ?>
                                    <?php endfor; ?>
                                    <strong><?= number_format($r['rating'], 1) ?>/5</strong>
                                </div>
                            </div>
                            <small class="text-muted">
                                <?= date('d/m/Y', strtotime($r['created_at'])) ?>
                            </small>
                        </div>

                        <?php if ($r['comment']): ?>
                            <p class="mb-0">
                                <i class="bi bi-chat-quote text-muted"></i>
                                "<?= htmlspecialchars($r['comment']) ?>"
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>