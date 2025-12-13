<?php
// ============================================
// FILE: views/admin/Staff/certificates.php
// ============================================
?>

<style>
    .cert-card {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.2s;
    }
    
    .cert-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }
    
    .cert-card.expired {
        border-color: #ef4444;
        background: #fef2f2;
    }
    
    .cert-card.pending {
        border-color: #f59e0b;
        background: #fffbeb;
    }
    
    .cert-icon {
        font-size: 3rem;
        color: #3b82f6;
    }
    
    .cert-card.expired .cert-icon {
        color: #ef4444;
    }
    
    .cert-card.pending .cert-icon {
        color: #f59e0b;
    }
</style>

<div class="container-fluid mt-4">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">🎓 Chứng chỉ - <?= htmlspecialchars($staff['full_name']) ?></h2>
            <p class="text-muted mb-0">
                <small>Email: <?= htmlspecialchars($staff['email']) ?> | SĐT: <?= htmlspecialchars($staff['phone']) ?></small>
            </p>
        </div>
        <div>
            <a href="index.php?act=admin-staff-cert-create&staff_id=<?= $staff['id'] ?>" 
               class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm chứng chỉ
            </a>
            <a href="index.php?act=admin-staff" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Thông báo -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Danh sách chứng chỉ -->
    <?php if (empty($certificates)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-award" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">Chưa có chứng chỉ nào</h4>
                <p class="text-muted mb-4">Hãy thêm chứng chỉ đầu tiên cho HDV này!</p>
                <a href="index.php?act=admin-staff-cert-create&staff_id=<?= $staff['id'] ?>" 
                   class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Thêm chứng chỉ đầu tiên
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($certificates as $cert): ?>
                <?php
                $statusClass = '';
                $statusBadge = '';
                $statusIcon = '✅';
                
                switch ($cert['status']) {
                    case 'EXPIRED':
                        $statusClass = 'expired';
                        $statusBadge = '<span class="badge bg-danger">Hết hạn</span>';
                        $statusIcon = '❌';
                        break;
                    case 'PENDING_RENEWAL':
                        $statusClass = 'pending';
                        $statusBadge = '<span class="badge bg-warning">Sắp hết hạn</span>';
                        $statusIcon = '⚠️';
                        break;
                    default:
                        $statusBadge = '<span class="badge bg-success">Còn hiệu lực</span>';
                }
                
                $daysLeft = null;
                if (!empty($cert['expiry_date'])) {
                    $expiry = strtotime($cert['expiry_date']);
                    $today = strtotime('today');
                    $daysLeft = floor(($expiry - $today) / 86400);
                }
                ?>
                
                <div class="col-md-6 mb-3">
                    <div class="cert-card <?= $statusClass ?>">
                        <div class="row">
                            <div class="col-2 text-center">
                                <div class="cert-icon"><?= $statusIcon ?></div>
                            </div>
                            <div class="col-10">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1"><?= htmlspecialchars($cert['certificate_name']) ?></h5>
                                        <?= $statusBadge ?>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?act=admin-staff-cert-edit&id=<?= $cert['id'] ?>" 
                                           class="btn btn-warning" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="index.php?act=admin-staff-cert-delete&id=<?= $cert['id'] ?>&staff_id=<?= $staff['id'] ?>" 
                                           class="btn btn-danger" title="Xóa"
                                           onclick="return confirm('⚠️ Xóa chứng chỉ này?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <?php if (!empty($cert['certificate_number'])): ?>
                                    <p class="mb-1">
                                        <small class="text-muted">Số: </small>
                                        <strong><?= htmlspecialchars($cert['certificate_number']) ?></strong>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($cert['issuing_organization'])): ?>
                                    <p class="mb-1">
                                        <small class="text-muted">Đơn vị cấp: </small>
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
                                            <?php if ($daysLeft !== null): ?>
                                                <br>
                                                <?php if ($daysLeft < 0): ?>
                                                    <small class="text-danger">Đã hết hạn <?= abs($daysLeft) ?> ngày</small>
                                                <?php elseif ($daysLeft <= 30): ?>
                                                    <small class="text-warning">Còn <?= $daysLeft ?> ngày</small>
                                                <?php else: ?>
                                                    <small class="text-success">Còn <?= $daysLeft ?> ngày</small>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($cert['certificate_file'])): ?>
                                    <div class="mt-2">
                                        <a href="<?= htmlspecialchars($cert['certificate_file']) ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-file-earmark-pdf"></i> Xem file
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($cert['notes'])): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i>
                                            <?= nl2br(htmlspecialchars($cert['notes'])) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Thống kê -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?= count($certificates) ?></h3>
                        <p class="mb-0 text-muted">Tổng chứng chỉ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success">
                            <?= count(array_filter($certificates, fn($c) => $c['status'] === 'VALID')) ?>
                        </h3>
                        <p class="mb-0 text-muted">Còn hiệu lực</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-warning">
                            <?= count(array_filter($certificates, fn($c) => $c['status'] === 'PENDING_RENEWAL')) ?>
                        </h3>
                        <p class="mb-0 text-muted">Sắp hết hạn</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-danger">
                            <?= count(array_filter($certificates, fn($c) => $c['status'] === 'EXPIRED')) ?>
                        </h3>
                        <p class="mb-0 text-muted">Hết hạn</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">