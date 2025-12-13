<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        .tour-hero {
            height: 400px;
            background-size: cover;
            background-position: center;
            position: relative;
            border-radius: 16px;
            overflow: hidden;
        }

        .tour-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.6));
        }

        .tour-hero-content {
            position: absolute;
            bottom: 30px;
            left: 30px;
            color: white;
            z-index: 1;
        }

        .tour-hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .tour-badge {
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-right: 10px;
        }

        .info-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .info-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .info-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .price-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px;
            padding: 30px;
        }

        .price-value {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .itinerary-day {
            border-left: 3px solid #667eea;
            padding-left: 20px;
            margin-bottom: 25px;
            position: relative;
        }

        .itinerary-day::before {
            content: '';
            position: absolute;
            left: -9px;
            top: 5px;
            width: 15px;
            height: 15px;
            background: #667eea;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #667eea;
        }

        .day-number {
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }

        .schedule-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .stat-box {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            background: white;
            border: 2px solid #e9ecef;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .action-btn {
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body class="bg-light">

    <div class="container mt-4 mb-5">

        <!-- Header Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1"><i class="bi bi-info-circle"></i> Chi tiết Tour</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="?act=admin-tour">Quản lý Tour</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($tour['title'] ?? 'Chi tiết') ?></li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                <a href="?act=admin-tour-edit&id=<?= $tour['id'] ?>" class="btn btn-warning action-btn">
                    <i class="bi bi-pencil"></i> Sửa Tour
                </a>
                <a href="?act=admin-itinerary&tour_id=<?= $tour['id'] ?>" class="btn btn-info action-btn">
                    <i class="bi bi-calendar-week"></i> Lịch trình
                </a>
                <a href="?act=admin-tour" class="btn btn-secondary action-btn">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <?php
        // Xử lý đường dẫn ảnh
        $imageSrc = !empty($tour['image_url']) ? $tour['image_url'] : 'placeholder.jpg';

        // Nếu chỉ là tên file → thêm folder assets/images và từ root
        if (!preg_match('#^(https?:)?//#i', $imageSrc) && !str_starts_with($imageSrc, '/')) {
            $imageSrc = '/assets/images/' . ltrim($imageSrc, '/');
        }

        // Fallback nếu muốn mặc định ảnh placeholder
        $defaultImage = '/assets/images/placeholder.jpg';
        $imageSrc = $imageSrc ?: $defaultImage;
        ?>

        <div class="tour-hero mb-4" style="background-image: url('<?= htmlspecialchars($imageSrc) ?>');">
            <div class="tour-hero-content">
                <h1 class="tour-hero-title"><?= htmlspecialchars($tour['title'] ?? 'Chưa có tiêu đề') ?></h1>
                <div>
                    <span class="tour-badge">
                        <i class="bi bi-tag"></i> <?= htmlspecialchars($tour['code'] ?? '') ?>
                    </span>
                    <span class="tour-badge">
                        <i class="bi bi-clock"></i> <?= (int) ($tour['duration_days'] ?? 0) ?> ngày
                    </span>
                    <span class="tour-badge">
                        <i class="bi bi-folder"></i> <?= htmlspecialchars($tour['category_name'] ?? 'Chưa phân loại') ?>
                    </span>
                </div>
            </div>
        </div>


        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">

                <!-- Quick Info Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="info-card card h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="info-icon bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="bi bi-calendar-event"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Thời gian</h6>
                                    <strong><?= $tour['duration_days'] ?> ngày</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="info-card card h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="info-icon bg-success bg-opacity-10 text-success me-3">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Người lớn</h6>
                                    <strong><?= number_format($tour['adult_price']) ?>đ</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="info-card card h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="info-icon bg-warning bg-opacity-10 text-warning me-3">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Trẻ em</h6>
                                    <strong><?= number_format($tour['child_price']) ?>đ</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="card info-card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-file-text"></i> Mô tả Tour</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($tour['short_desc'])): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> <strong>Tóm tắt:</strong><br>
                                <?= nl2br(htmlspecialchars($tour['short_desc'])) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($tour['full_desc'])): ?>
                            <div class="tour-description">
                                <?= nl2br(htmlspecialchars($tour['full_desc'])) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Chưa có mô tả chi tiết.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Itinerary -->
                <?php if (!empty($itineraries)): ?>
                    <div class="card info-card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-map"></i> Lịch trình chi tiết
                                <span class="badge bg-primary"><?= count($itineraries) ?> ngày</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($itineraries as $day): ?>
                                <div class="itinerary-day">
                                    <div class="day-number">Ngày <?= $day['day_number'] ?></div>
                                    <h5><?= htmlspecialchars($day['title']) ?></h5>

                                    <?php if (!empty($day['description'])): ?>
                                        <p class="text-muted"><?= nl2br(htmlspecialchars($day['description'])) ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($day['activities'])): ?>
                                        <div class="schedule-item">
                                            <strong><i class="bi bi-list-check"></i> Hoạt động:</strong>
                                            <div class="mt-2" style="white-space: pre-line;">
                                                <?= htmlspecialchars($day['activities']) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="row mt-2">
                                        <?php if (!empty($day['accommodation'])): ?>
                                            <div class="col-md-6">
                                                <small>
                                                    <i class="bi bi-house-door text-primary"></i>
                                                    <strong>Nghỉ:</strong> <?= htmlspecialchars($day['accommodation']) ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($day['meals'])): ?>
                                            <div class="col-md-6">
                                                <small>
                                                    <i class="bi bi-cup-hot text-success"></i>
                                                    <strong>Ăn:</strong> <?= htmlspecialchars($day['meals']) ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Policy -->
                <?php if (!empty($tour['policy'])): ?>
                    <div class="card info-card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-shield-check"></i> Chính sách</h5>
                        </div>
                        <div class="card-body">
                            <?= nl2br(htmlspecialchars($tour['policy'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Right Column -->
            <div class="col-lg-4">

                <!-- Price Card -->
                <div class="price-card mb-4">
                    <h5 class="mb-3"><i class="bi bi-cash-coin"></i> Bảng giá</h5>
                    <div class="mb-3">
                        <small class="opacity-75">Người lớn</small>
                        <div class="price-value"><?= number_format($tour['adult_price']) ?>đ</div>
                    </div>
                    <div class="mb-3">
                        <small class="opacity-75">Trẻ em (dưới 10 tuổi)</small>
                        <div class="price-value"><?= number_format($tour['child_price']) ?>đ</div>
                    </div>
                    <hr class="border-white opacity-25">
                    <small class="opacity-75">
                        <i class="bi bi-info-circle"></i> Giá có thể thay đổi theo mùa
                    </small>
                </div>

                <!-- Statistics -->
                <div class="card info-card mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-graph-up"></i> Thống kê</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-box">
                                    <div class="stat-value"><?= $stats['total_schedules'] ?? 0 ?></div>
                                    <div class="stat-label">Lịch khởi hành</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box">
                                    <div class="stat-value"><?= $stats['total_bookings'] ?? 0 ?></div>
                                    <div class="stat-label">Booking</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box">
                                    <div class="stat-value"><?= $stats['total_customers'] ?? 0 ?></div>
                                    <div class="stat-label">Khách tham gia</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box">
                                    <div class="stat-value"><?= number_format($stats['total_revenue'] ?? 0) ?>đ</div>
                                    <div class="stat-label">Doanh thu</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="card info-card mb-4">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="bi bi-flag"></i> Trạng thái</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Hiển thị:</span>
                            <?php if ($tour['is_active']): ?>
                                <span class="badge bg-success fs-6">✓ Đang hiển thị</span>
                            <?php else: ?>
                                <span class="badge bg-secondary fs-6">✕ Đã ẩn</span>
                            <?php endif; ?>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Có lịch mở:</span>
                            <?php if ($tour['display_status'] === 'Hiển thị'): ?>
                                <span class="badge bg-primary fs-6">✓ Có</span>
                            <?php else: ?>
                                <span class="badge bg-warning fs-6">✕ Chưa có</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card info-card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-lightning"></i> Thao tác nhanh</h6>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <a href="?act=admin-schedule&keyword=<?= urlencode($tour['code']) ?>"
                            class="btn btn-outline-primary">
                            <i class="bi bi-calendar-plus"></i> Quản lý lịch khởi hành
                        </a>
                        <a href="?act=admin-itinerary&tour_id=<?= $tour['id'] ?>" class="btn btn-outline-info">
                            <i class="bi bi-calendar-week"></i> Quản lý lịch trình
                        </a>
                        <a href="?act=admin-booking-create" class="btn btn-outline-success">
                            <i class="bi bi-plus-circle"></i> Tạo Booking mới
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>