<?php
// ============================================
// FILE 2: views/admin/CheckIn/guests.php
// ============================================
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-person-check"></i> Check-in Khách
            </h2>
            <p class="text-muted mb-0">
                Booking: <strong>#<?= htmlspecialchars($bookingInfo['booking_code']) ?></strong> - 
                <?= htmlspecialchars($bookingInfo['contact_name']) ?>
            </p>
        </div>
        <a href="?act=admin-checkin-today" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Booking Summary -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6><i class="bi bi-info-circle"></i> Thông tin tour</h6>
                    <p class="mb-1">
                        <strong><?= htmlspecialchars($bookingInfo['tour_name']) ?></strong>
                    </p>
                    <small class="text-muted">
                        <?= date('d/m/Y', strtotime($bookingInfo['depart_date'])) ?>
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6><i class="bi bi-people"></i> Số lượng khách</h6>
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-4 fw-bold"><?= $bookingInfo['adults'] ?></div>
                            <small>Người lớn</small>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold"><?= $bookingInfo['children'] ?></div>
                            <small>Trẻ em</small>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold"><?= $bookingInfo['checked_in_count'] ?></div>
                            <small>Đã check-in</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Check-in All -->
    <?php if ($bookingInfo['checked_in_count'] == 0): ?>
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <span><i class="bi bi-lightning"></i> Check-in nhanh toàn bộ?</span>
            <form method="POST" action="?act=admin-checkin-quick-all" class="d-inline">
                <input type="hidden" name="booking_id" value="<?= $bookingInfo['booking_id'] ?>">
                <button type="submit" class="btn btn-warning" 
                        onclick="return confirm('Tự động check-in tất cả?')">
                    <i class="bi bi-lightning-charge"></i> Check-in nhanh
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Manual Check-in Form -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-person-plus"></i> Thêm khách check-in</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="?act=admin-checkin-guest-store">
                <input type="hidden" name="booking_id" value="<?= $bookingInfo['booking_id'] ?>">
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tên khách <span class="text-danger">*</span></label>
                        <input type="text" name="guest_name" class="form-control" required>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Loại <span class="text-danger">*</span></label>
                        <select name="guest_type" class="form-select" required>
                            <option value="ADULT">Người lớn</option>
                            <option value="CHILD">Trẻ em</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">CMND/CCCD</label>
                        <input type="text" name="id_number" class="form-control">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">SĐT</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Check-in khách này
                </button>
            </form>
        </div>
    </div>

    <!-- Checked-in Guests List -->
    <?php if (!empty($checkedInGuests)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Danh sách đã check-in</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Tên khách</th>
                            <th>Loại</th>
                            <th>CMND</th>
                            <th>SĐT</th>
                            <th>Trạng thái</th>
                            <th>Thời gian</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checkedInGuests as $i => $guest): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($guest['guest_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $guest['guest_type'] == 'ADULT' ? 'primary' : 'info' ?>">
                                        <?= $guest['guest_type'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($guest['id_number'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($guest['phone'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-success"><?= $guest['check_in_status'] ?></span>
                                </td>
                                <td>
                                    <small><?= date('H:i', strtotime($guest['check_in_time'])) ?></small>
                                </td>
                                <td>
                                    <a href="?act=admin-checkin-guest-delete&id=<?= $guest['id'] ?>&booking_id=<?= $bookingInfo['booking_id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Xóa?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>