<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#info">Thông tin</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#certificates">
            Chứng chỉ 
            <span class="badge bg-primary"><?= count($certificates ?? []) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#history">
            Lịch sử tour
            <span class="badge bg-success"><?= count($history ?? []) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#ratings">
            Đánh giá
            <span class="badge bg-warning"><?= count($ratings ?? []) ?></span>
        </a>
    </li>
</ul>

<div class="tab-content">
    <div id="info" class="tab-pane fade show active">
        <!-- Thông tin hiện tại -->
    </div>
    
    <div id="certificates" class="tab-pane fade">
        <a href="index.php?act=admin-staff-cert&staff_id=<?= $staff['id'] ?>" 
           class="btn btn-primary">
            Quản lý chứng chỉ
        </a>
    </div>
    
    <div id="history" class="tab-pane fade">
        <a href="index.php?act=admin-staff-performance&staff_id=<?= $staff['id'] ?>" 
           class="btn btn-primary">
            Xem hiệu suất
        </a>
    </div>
    
    <div id="ratings" class="tab-pane fade">
        <a href="index.php?act=admin-staff-rating&staff_id=<?= $staff['id'] ?>" 
           class="btn btn-primary">
            Xem đánh giá
        </a>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
