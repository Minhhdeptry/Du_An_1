<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075);
    }

    .table-hover thead tr:hover {
        background-color: #343a40 !important;
    }

    .staff-avatar {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #ddd;
    }

    .staff-type-badge {
        font-size: 0.85rem;
        padding: 0.35rem 0.6rem;
    }

    .rating-stars {
        color: #ffc107;
        font-size: 0.9rem;
    }

    .empty-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
    }

    .health-icon {
        font-size: 1.2rem;
    }
</style>

<div class="container-fluid px-4 mt-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="page-title mb-1">👥 Quản lý Hướng dẫn viên</h1>
            <p class="text-muted mb-0">
                <small>Tổng: <strong><?= count($staffs) ?></strong> HDV</small>
            </p>
        </div>
        <a href="index.php?act=admin-staff-create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Thêm HDV
        </a>
    </div>

    <!-- SEARCH & FILTER -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form class="row g-2" method="get" action="index.php">
                <input type="hidden" name="act" value="admin-staff">

                <!-- Tìm kiếm -->
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="keyword" class="form-control border-start-0"
                            placeholder="Tìm theo tên, email, SĐT, CMND..."
                            value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                    </div>
                </div>

                <!-- Lọc theo phân loại -->
                <div class="col-md-2">
                    <select name="staff_type" class="form-select">
                        <option value="">Tất cả phân loại</option>
                        <option value="DOMESTIC" <?= ($_GET['staff_type'] ?? '') == 'DOMESTIC' ? 'selected' : '' ?>>
                            🏠 Nội địa
                        </option>
                        <option value="INTERNATIONAL" <?= ($_GET['staff_type'] ?? '') == 'INTERNATIONAL' ? 'selected' : '' ?>>
                            ✈️ Quốc tế
                        </option>
                        <option value="SPECIALIZED" <?= ($_GET['staff_type'] ?? '') == 'SPECIALIZED' ? 'selected' : '' ?>>
                            🎯 Chuyên tuyến
                        </option>
                        <option value="GROUP_TOUR" <?= ($_GET['staff_type'] ?? '') == 'GROUP_TOUR' ? 'selected' : '' ?>>
                            👥 Khách đoàn
                        </option>
                    </select>
                </div>

                <!-- Lọc theo trạng thái -->
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="ACTIVE" <?= ($_GET['status'] ?? '') == 'ACTIVE' ? 'selected' : '' ?>>
                            ✅ Đang làm
                        </option>
                        <option value="INACTIVE" <?= ($_GET['status'] ?? '') == 'INACTIVE' ? 'selected' : '' ?>>
                            ⏸️ Nghỉ việc
                        </option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Tìm kiếm
                    </button>
                </div>

                <?php if (!empty($_GET['keyword']) || !empty($_GET['staff_type']) || !empty($_GET['status'])): ?>
                    <div class="col-md-2">
                        <a href="index.php?act=admin-staff" class="btn btn-secondary w-100">
                            <i class="bi bi-x-circle"></i> Xóa bộ lọc
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- STATS CARDS (Optional) -->
    <?php
    $stats = [
        'active' => count(array_filter($staffs, fn($s) => $s['status'] === 'ACTIVE')),
        'inactive' => count(array_filter($staffs, fn($s) => $s['status'] === 'INACTIVE')),
        'avg_rating' => !empty($staffs) ? round(array_sum(array_column($staffs, 'rating')) / count($staffs), 1) : 0,
        'total_experience' => array_sum(array_column($staffs, 'experience_years'))
    ];
    ?>
    <!--  -->

    <!-- TABLE -->
    <?php if (empty($staffs)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">Chưa có hướng dẫn viên nào</h4>
                <p class="text-muted">
                    <?php if (!empty($_GET['keyword'])): ?>
                        Không tìm thấy kết quả cho "<strong><?= htmlspecialchars($_GET['keyword']) ?></strong>"
                    <?php else: ?>
                        Hãy thêm HDV đầu tiên
                    <?php endif; ?>
                </p>
                <a href="index.php?act=admin-staff-create" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-circle"></i> Thêm HDV đầu tiên
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">STT</th>
                            <th width="80">Ảnh</th>
                            <th width="180">Họ tên</th>
                            <th width="120">Phân loại</th>
                            <th width="150">Ngôn ngữ</th>
                            <th width="80" class="text-center">KN</th>
                            <th width="80" class="text-center">Đánh giá</th>
                            <th width="80" class="text-center">Sức khoẻ</th>
                            <th width="150">Liên hệ</th>
                            <th width="120">Trình độ</th>
                            <th width="100" class="text-center">Trạng thái</th>
                            <th width="180" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staffs as $i => $s): ?>
                            <tr>
                                <!-- STT -->
                                <td><?= $i + 1 ?></td>

                                <!-- Ảnh -->
                                <td>
                                    <?php if (!empty($s['profile_image'])): ?>
                                        <?php
                                        $image = trim($s['profile_image']);

                                        // Xử lý đường dẫn ảnh
                                        if (preg_match('#^(https?:)?//#i', $image) || str_starts_with($image, '/')) {
                                            // URL đầy đủ hoặc absolute path
                                            $imageSrc = $image;
                                        } elseif (str_contains($image, 'assets/')) {
                                            // Đã có assets/ trong path
                                            $imageSrc = $image;
                                        } else {
                                            // Chỉ là tên file → thêm prefix
                                            $imageSrc = 'assets/images/staff/' . $image;
                                        }
                                        ?>
                                        <img src="<?= htmlspecialchars($imageSrc) ?>" alt="<?= htmlspecialchars($s['full_name']) ?>"
                                            class="staff-avatar"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="empty-avatar" style="display: none;">
                                            <?= strtoupper(substr($s['full_name'], 0, 1)) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-avatar">
                                            <?= strtoupper(substr($s['full_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Họ tên -->
                                <td>
                                    <strong><?= htmlspecialchars($s['full_name']) ?></strong>
                                    <?php if (!empty($s['date_of_birth'])): ?>
                                        <br><small class="text-muted">
                                            <?= date('d/m/Y', strtotime($s['date_of_birth'])) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>

                                <!-- Phân loại -->
                                <td>
                                    <?php
                                    $typeLabels = [
                                        'DOMESTIC' => ['text' => '🏠 Nội địa', 'class' => 'bg-primary'],
                                        'INTERNATIONAL' => ['text' => '✈️ Quốc tế', 'class' => 'bg-success'],
                                        'SPECIALIZED' => ['text' => '🎯 Chuyên tuyến', 'class' => 'bg-info'],
                                        'GROUP_TOUR' => ['text' => '👥 Khách đoàn', 'class' => 'bg-warning text-dark']
                                    ];
                                    $type = $typeLabels[$s['staff_type']] ?? ['text' => $s['staff_type'], 'class' => 'bg-secondary'];
                                    ?>
                                    <span class="badge <?= $type['class'] ?> staff-type-badge">
                                        <?= $type['text'] ?>
                                    </span>
                                </td>

                                <!-- Ngôn ngữ -->
                                <td>
                                    <?php if (!empty($s['languages'])): ?>
                                        <small><?= htmlspecialchars($s['languages']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Kinh nghiệm -->
                                <td class="text-center">
                                    <span class="badge bg-info">
                                        <?= (int) ($s['experience_years'] ?? 0) ?> năm
                                    </span>
                                </td>

                                <!-- Đánh giá -->
                                <td class="text-center">
                                    <?php if (!empty($s['rating'])): ?>
                                        <span class="rating-stars">
                                            ⭐ <?= number_format($s['rating'], 1) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Sức khoẻ -->
                                <td class="text-center">
                                    <?php
                                    $healthIcons = [
                                        'good' => '💚',
                                        'fair' => '💛',
                                        'poor' => '❤️'
                                    ];
                                    echo '<span class="health-icon" title="' . ($s['health_status'] ?? 'good') . '">' .
                                        ($healthIcons[$s['health_status'] ?? 'good'] ?? '💚') . '</span>';
                                    ?>
                                </td>

                                <!-- Liên hệ -->
                                <td>
                                    <small>
                                        📧 <?= htmlspecialchars($s['email']) ?><br>
                                        📱 <?= htmlspecialchars($s['phone']) ?>
                                    </small>
                                </td>

                                <!-- Trình độ -->
                                <td>
                                    <small><?= htmlspecialchars($s['qualification'] ?? '-') ?></small>
                                </td>

                                <!-- Trạng thái -->
                                <td class="text-center">
                                    <?php if ($s['status'] == 'ACTIVE'): ?>
                                        <span class="badge bg-success">✅ Đang làm</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">⏸️ Nghỉ việc</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Thao tác -->
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="index.php?act=admin-staff-detail&id=<?= $s['id'] ?>" class="btn btn-info"
                                            title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="index.php?act=admin-staff-performance&id=<?= $s['id'] ?>"
                                            class="btn btn-primary" title="Hiệu suất">
                                            <i class="bi bi-graph-up"></i>
                                        </a>

                                        <a href="index.php?act=admin-staff-edit&id=<?= $s['id'] ?>" class="btn btn-secondary"
                                            title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <a href="index.php?act=admin-staff-delete&id=<?= $s['id'] ?>" class="btn btn-danger"
                                            title="Xóa" onclick="return confirm('⚠️ Xóa HDV này?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">