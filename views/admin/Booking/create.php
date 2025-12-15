<!-- views/admin/Booking/create.php - HOÀN CHỈNH -->
<?php
$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">➕ Tạo Booking mới</h2>
        <a href="index.php?act=admin-booking" class="btn btn-secondary">← Quay lại</a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- ✅ CHẾ ĐỘ TẠO BOOKING -->
    <div class="card mb-3 shadow-sm border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">🎯 Chọn loại booking</h5>
        </div>
        <div class="card-body">
            <div class="btn-group w-100 mb-3" role="group">
                <input type="radio" class="btn-check" name="booking_mode" id="mode_scheduled" value="scheduled" checked
                    onclick="switchMode('scheduled')">
                <label class="btn btn-outline-primary btn-lg" for="mode_scheduled">
                    <i class="bi bi-calendar-check"></i> Đặt theo lịch có sẵn
                    <br><small>Chọn từ các tour đang mở</small>
                </label>

                <input type="radio" class="btn-check" name="booking_mode" id="mode_custom" value="custom"
                    onclick="switchMode('custom')">
                <label class="btn btn-outline-success btn-lg" for="mode_custom">
                    <i class="bi bi-pencil-square"></i> Tạo tour theo yêu cầu
                    <br><small>Tự do tùy chỉnh thông tin</small>
                </label>
            </div>
        </div>
    </div>

    <form action="index.php?act=admin-booking-store" method="POST" id="bookingForm">
        <!-- =============================================
             🔵 CHẾ ĐỘ 1: ĐẶT THEO LỊCH CÓ SẴN
             ============================================= -->
        <div id="scheduledMode">
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📅 Chọn lịch tour</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lịch khởi hành <span class="text-danger">*</span></label>

                        <select name="tour_schedule_id" id="tour_schedule_select" class="form-select form-select-lg">
                            <option value="">-- Chọn lịch tour --</option>

                            <?php
                            // ✅ PHÂN LOẠI TOUR
                            $upcomingRegular = [];
                            $upcomingCustom = [];
                            $pastRegular = [];
                            $pastCustom = [];

                            $today = date('Y-m-d');

                            foreach ($schedules as $sc) {
                                $seatsTotal = (int) ($sc['seats_total'] ?? 0);
                                $isCustomFlag = (int) ($sc['is_custom_request'] ?? 0);
                                $isCustomRequest = ($seatsTotal === 0 || $isCustomFlag === 1);

                                $departDate = $sc['depart_date'];
                                $isPast = (strtotime($departDate) < strtotime($today));

                                if ($isPast) {
                                    if ($isCustomRequest) {
                                        $pastCustom[] = $sc;
                                    } else {
                                        $pastRegular[] = $sc;
                                    }
                                } else {
                                    if ($isCustomRequest) {
                                        $upcomingCustom[] = $sc;
                                    } else {
                                        $upcomingRegular[] = $sc;
                                    }
                                }
                            }
                            ?>

                            <!-- ✅ NHÓM 1: TOUR THƯỜNG SẮP KHỞI HÀNH -->
                            <?php if (!empty($upcomingRegular)): ?>
                                <optgroup label="📁 TOUR THƯỜNG - Sắp khởi hành (<?= count($upcomingRegular) ?> tour)">
                                    <?php foreach ($upcomingRegular as $sc): ?>
                                        <?php
                                        $tourTitle = htmlspecialchars($sc['tour_title'] ?? '');
                                        $category = htmlspecialchars($sc['category_name'] ?? '');
                                        $departDate = date('d/m/Y', strtotime($sc['depart_date']));
                                        $duration = (int) ($sc['duration_days'] ?? 0);
                                        $seatsAvail = (int) ($sc['seats_available'] ?? 0);
                                        $seatsTotal = (int) ($sc['seats_total'] ?? 0);
                                        $priceAdult = (float) ($sc['price_adult'] ?? 0);
                                        $priceChildren = (float) ($sc['price_children'] ?? 0);

                                        $disabled = ($seatsAvail <= 0) ? 'disabled' : '';
                                        $seatsText = $seatsAvail > 0
                                            ? "✅ Còn {$seatsAvail}/{$seatsTotal}"
                                            : "❌ Hết chỗ";

                                        $daysUntil = floor((strtotime($sc['depart_date']) - strtotime($today)) / 86400);
                                        $daysText = $daysUntil == 0 ? "HÔM NAY" : ($daysUntil == 1 ? "MAI" : "Còn {$daysUntil} ngày");
                                        ?>

                                        <option value="<?= $sc['id'] ?>" data-duration="<?= $duration ?>"
                                            data-price-adult="<?= $priceAdult ?>" data-price-children="<?= $priceChildren ?>"
                                            data-seats-available="<?= $seatsAvail ?>" data-seats-total="<?= $seatsTotal ?>"
                                            data-is-custom="0" data-is-past="0" <?= $disabled ?>>

                                            [<?= $category ?>] <?= $tourTitle ?>
                                            │ 📅 <?= $departDate ?> (<?= $daysText ?>)
                                            │ <?= $seatsText ?>
                                            <!-- │ 💰 <?= number_format($priceAdult) ?>đ/NL -->

                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>

                            <!-- ✅ NHÓM 2: TOUR CUSTOM SẮP KHỞI HÀNH -->
                            <?php if (!empty($upcomingCustom)): ?>
                                <optgroup label="🎯 TOUR THEO YÊU CẦU - Sắp khởi hành (<?= count($upcomingCustom) ?> tour)">
                                    <?php foreach ($upcomingCustom as $sc): ?>
                                        <?php
                                        $tourTitle = htmlspecialchars($sc['tour_title'] ?? '');
                                        $category = htmlspecialchars($sc['category_name'] ?? '');
                                        $departDate = date('d/m/Y', strtotime($sc['depart_date']));
                                        $duration = (int) ($sc['duration_days'] ?? 0);
                                        $priceAdult = (float) ($sc['price_adult'] ?? 0);
                                        $priceChildren = (float) ($sc['price_children'] ?? 0);

                                        $daysUntil = floor((strtotime($sc['depart_date']) - strtotime($today)) / 86400);
                                        $daysText = $daysUntil == 0 ? "HÔM NAY" : ($daysUntil == 1 ? "MAI" : "Còn {$daysUntil} ngày");
                                        ?>

                                        <option value="<?= $sc['id'] ?>" data-duration="<?= $duration ?>"
                                            data-price-adult="<?= $priceAdult ?>" data-price-children="<?= $priceChildren ?>"
                                            data-seats-available="0" data-seats-total="0" data-is-custom="1" data-is-past="0">

                                            [<?= $category ?>] 🔖 <?= $tourTitle ?>
                                            │ 📅 <?= $departDate ?> (<?= $daysText ?>)
                                            │ ♾️ Không giới hạn
                                            <!-- │ 💰 <?= number_format($priceAdult) ?>đ/NL -->

                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>

                            <!-- ✅ NHÓM 3: TOUR THƯỜNG ĐÃ KHỞI HÀNH -->
                            <?php if (!empty($pastRegular)): ?>
                                <optgroup label="⚠️ TOUR THƯỜNG - Đã khởi hành (<?= count($pastRegular) ?> tour)">
                                    <?php foreach ($pastRegular as $sc): ?>
                                        <?php
                                        $tourTitle = htmlspecialchars($sc['tour_title'] ?? '');
                                        $category = htmlspecialchars($sc['category_name'] ?? '');
                                        $departDate = date('d/m/Y', strtotime($sc['depart_date']));
                                        $duration = (int) ($sc['duration_days'] ?? 0);
                                        $seatsAvail = (int) ($sc['seats_available'] ?? 0);
                                        $seatsTotal = (int) ($sc['seats_total'] ?? 0);
                                        $priceAdult = (float) ($sc['price_adult'] ?? 0);
                                        $priceChildren = (float) ($sc['price_children'] ?? 0);

                                        $disabled = ($seatsAvail <= 0) ? 'disabled' : '';
                                        $seatsText = $seatsAvail > 0 ? "✅ Còn {$seatsAvail}/{$seatsTotal}" : "❌ Hết chỗ";
                                        $daysAgo = floor((strtotime($today) - strtotime($sc['depart_date'])) / 86400);
                                        $daysText = $daysAgo == 0 ? "HÔM NAY" : "Đã qua {$daysAgo} ngày";
                                        ?>

                                        <option value="<?= $sc['id'] ?>" data-duration="<?= $duration ?>"
                                            data-price-adult="<?= $priceAdult ?>" data-price-children="<?= $priceChildren ?>"
                                            data-seats-available="<?= $seatsAvail ?>" data-seats-total="<?= $seatsTotal ?>"
                                            data-is-custom="0" data-is-past="1" <?= $disabled ?>>

                                            [<?= $category ?>] ⏰ <?= $tourTitle ?>
                                            │ 📅 <?= $departDate ?> (<?= $daysText ?>)
                                            │ <?= $seatsText ?>
                                            <!-- │ 💰 <?= number_format($priceAdult) ?>đ/NL -->

                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>

                            <!-- ✅ NHÓM 4: TOUR CUSTOM ĐÃ KHỞI HÀNH -->
                            <?php if (!empty($pastCustom)): ?>
                                <optgroup label="⚠️ TOUR THEO YÊU CẦU - Đã khởi hành (<?= count($pastCustom) ?> tour)">
                                    <?php foreach ($pastCustom as $sc): ?>
                                        <?php
                                        $tourTitle = htmlspecialchars($sc['tour_title'] ?? '');
                                        $category = htmlspecialchars($sc['category_name'] ?? '');
                                        $departDate = date('d/m/Y', strtotime($sc['depart_date']));
                                        $duration = (int) ($sc['duration_days'] ?? 0);
                                        $priceAdult = (float) ($sc['price_adult'] ?? 0);
                                        $priceChildren = (float) ($sc['price_children'] ?? 0);

                                        $daysAgo = floor((strtotime($today) - strtotime($sc['depart_date'])) / 86400);
                                        $daysText = $daysAgo == 0 ? "HÔM NAY" : "Đã qua {$daysAgo} ngày";
                                        ?>

                                        <option value="<?= $sc['id'] ?>" data-duration="<?= $duration ?>"
                                            data-price-adult="<?= $priceAdult ?>" data-price-children="<?= $priceChildren ?>"
                                            data-seats-available="0" data-seats-total="0" data-is-custom="1" data-is-past="1">

                                            [<?= $category ?>] 🔖 <?= $tourTitle ?>
                                            │ 📅 <?= $departDate ?> (<?= $daysText ?>)
                                            │ ♾️ Không giới hạn
                                            │ 💰 <?= number_format($priceAdult) ?>đ/NL

                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>

                        </select>

                        <small class="text-muted mt-2 d-block">
                            <i class="bi bi-info-circle"></i>
                            <strong>Chú thích:</strong>
                            📁 = Tour thường │ 🎯 = Tour theo yêu cầu │ ⚠️ = Đã khởi hành
                        </small>
                    </div>

                    <!-- ✅ THÔNG TIN TOUR (HIỂN THỊ + CHO SỬA) -->
                    <div class="alert alert-info mt-3" id="schedule_info_banner" style="display:none;">
                        <strong><i class="bi bi-info-circle"></i> Thông tin từ lịch tour:</strong>
                        <span id="schedule_info_text"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- =============================================
             🟢 CHẾ ĐỘ 2: TẠO TOUR THEO YÊU CẦU
             ============================================= -->
        <div id="customMode" style="display:none;">
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">✏️ Thông tin tour theo yêu cầu</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên tour <span class="text-danger">*</span></label>
                        <input type="text" name="custom_tour_name" id="custom_tour_name" class="form-control"
                            placeholder="Vd: Tour Phú Quốc 4N3Đ"
                            value="<?= htmlspecialchars($old['custom_tour_name'] ?? '') ?>">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày khởi hành <span class="text-danger">*</span></label>
                            <input type="date" name="depart_date" id="custom_depart_date" class="form-control"
                                min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($old['depart_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày về</label>
                            <input type="date" name="return_date" id="custom_return_date" class="form-control"
                                min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($old['return_date'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ GIÁ TOUR (CHUNG - TỰ ĐỘNG FILL NHƯNG CHO SỬA) -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">💰 Giá tour</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá người lớn (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="price_adult" id="price_adult" class="form-control form-control-lg"
                            min="0" step="1000" required oninput="updateTotals()"
                            value="<?= htmlspecialchars($old['price_adult'] ?? '') ?>"
                            placeholder="Nhập giá hoặc chọn từ lịch tour">
                        <small class="text-muted">Tự động điền khi chọn lịch, có thể sửa</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Giá trẻ em (VNĐ)</label>
                        <input type="number" name="price_children" id="price_children"
                            class="form-control form-control-lg" min="0" step="1000" oninput="updateTotals()"
                            value="<?= htmlspecialchars($old['price_children'] ?? '0') ?>"
                            placeholder="Nhập giá hoặc chọn từ lịch tour">
                        <small class="text-muted">Tự động điền khi chọn lịch, có thể sửa</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- =============================================
             THÔNG TIN KHÁCH HÀNG
             ============================================= -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-warning">
                <h5 class="mb-0">👤 Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" name="contact_name" class="form-control" required
                            value="<?= htmlspecialchars($old['contact_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="contact_phone" class="form-control" required
                            value="<?= htmlspecialchars($old['contact_phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="contact_email" class="form-control"
                            value="<?= htmlspecialchars($old['contact_email'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Người lớn <span class="text-danger">*</span></label>
                        <input type="number" name="adults" id="adults" class="form-control" min="1" value="1" required
                            oninput="updateTotals()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Trẻ em</label>
                        <input type="number" name="children" id="children" class="form-control" min="0" value="0"
                            oninput="updateTotals()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tổng người</label>
                        <input type="number" id="total_people" class="form-control bg-light" value="1" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- YÊU CẦU ĐẶC BIỆT -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">📝 Yêu cầu đặc biệt</h5>
            </div>
            <div class="card-body">
                <textarea name="special_request" class="form-control" rows="3"
                    placeholder="Ghi chú đặc biệt từ khách hàng..."><?= htmlspecialchars($old['special_request'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- ✅ DỊCH VỤ BỔ SUNG -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">🛎️ Dịch vụ bổ sung (tùy chọn)</h5>
            </div>
            <div class="card-body">
                <div id="items-container"></div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItemRow()">
                    <i class="bi bi-plus-circle"></i> Thêm dịch vụ
                </button>
            </div>
        </div>

        <!-- TỔNG TIỀN -->
        <div class="card mb-3 shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">💵 Tổng thanh toán</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Tiền tour:</strong> <span id="tour_amount">0</span> đ</p>
                        <p class="mb-2"><strong>Dịch vụ bổ sung:</strong> <span id="items_amount">0</span> đ</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h3 class="mb-0 fw-bold text-danger">
                            TỔNG: <span id="total_amount">0</span> đ
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-check-circle me-2"></i>Tạo booking
            </button>
            <a href="index.php?act=admin-booking" class="btn btn-secondary btn-lg">
                <i class="bi bi-x-circle me-2"></i>Hủy
            </a>
        </div>
    </form>
</div>

<script>
    let currentMode = 'scheduled';
    let itemIndex = 0;

    function switchMode(mode) {
        currentMode = mode;
        const scheduledMode = document.getElementById('scheduledMode');
        const customMode = document.getElementById('customMode');
        const scheduleSelect = document.getElementById('tour_schedule_select');
        const customFields = ['custom_tour_name', 'custom_depart_date'];

        if (mode === 'scheduled') {
            scheduledMode.style.display = 'block';
            customMode.style.display = 'none';
            scheduleSelect.required = true;
            customFields.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.required = false;
            });
        } else {
            scheduledMode.style.display = 'none';
            customMode.style.display = 'block';
            scheduleSelect.required = false;
            scheduleSelect.value = '';
            document.getElementById('schedule_info_banner').style.display = 'none';
            customFields.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.required = true;
            });
        }
        updateTotals();
    }

    // ✅ XỬ LÝ KHI CHỌN LỊCH TOUR
    document.getElementById('tour_schedule_select').addEventListener('change', function () {
        const selected = this.selectedOptions[0];
        const banner = document.getElementById('schedule_info_banner');
        const infoText = document.getElementById('schedule_info_text');

        if (selected && selected.value) {
            const priceAdult = parseFloat(selected.dataset.priceAdult || 0);
            const priceChildren = parseFloat(selected.dataset.priceChildren || 0);
            const isCustom = selected.dataset.isCustom === '1';
            const isPast = selected.dataset.isPast === '1';
            const seatsAvail = parseInt(selected.dataset.seatsAvailable || 0);
            const seatsTotal = parseInt(selected.dataset.seatsTotal || 0);

            // ✅ CẢNH BÁO NẾU TOUR QUÁ NGÀY
            if (isPast) {
                const confirmed = confirm(
                    '⚠️ CẢNH BÁO\n\n' +
                    'Bạn đang chọn tour ĐÃ KHỞI HÀNH!\n\n' +
                    'Đây là booking bổ sung cho tour đang diễn ra.\n\n' +
                    'Bạn có chắc chắn muốn tiếp tục?'
                );

                if (!confirmed) {
                    this.value = '';
                    banner.style.display = 'none';
                    return;
                }
            }

            // ✅ CẢNH BÁO NẾU TOUR THƯỜNG HẾT CHỖ
            if (!isCustom && seatsAvail <= 0) {
                alert('⚠️ CẢNH BÁO\n\nTour này đã HẾT CHỖ!\n\nVui lòng chọn lịch khởi hành khác.');
                this.value = '';
                banner.style.display = 'none';
                return;
            }

            // ✅ HIỂN THỊ THÔNG TIN
            let info = '';
            if (isCustom) {
                info = '🎯 <strong>Tour theo yêu cầu</strong> - ♾️ Không giới hạn chỗ';
            } else {
                info = `📁 <strong>Tour thường</strong> - Còn <strong>${seatsAvail}/${seatsTotal}</strong> chỗ`;
            }
            info += ` - Giá: <strong>${priceAdult.toLocaleString('vi-VN')}đ</strong>/NL, <strong>${priceChildren.toLocaleString('vi-VN')}đ</strong>/TE`;

            infoText.innerHTML = info;
            banner.style.display = 'block';

            // ✅ TỰ ĐỘNG ĐIỀN GIÁ (NHƯNG CHO PHÉP SỬA)
            document.getElementById('price_adult').value = priceAdult;
            document.getElementById('price_children').value = priceChildren;

            // Trigger update totals
            updateTotals();
        } else {
            banner.style.display = 'none';
        }
    });

    function addItemRow() {
        const container = document.getElementById('items-container');
        const row = document.createElement('div');
        row.className = 'item-row card mb-3 border-start border-4 border-info';
        row.innerHTML = `
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Tên dịch vụ</label>
                    <input type="text" name="items[${itemIndex}][description]" 
                        class="form-control" placeholder="VD: Phòng đơn, Bảo hiểm...">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Loại</label>
                    <select name="items[${itemIndex}][type]" class="form-select">
                        <option value="SERVICE">🔧 Dịch vụ</option>
                        <option value="MEAL">🍽️ Bữa ăn</option>
                        <option value="ROOM">🏨 Phòng đơn</option>
                        <option value="INSURANCE">🛡️ Bảo hiểm</option>
                        <option value="TRANSPORT">🚗 Phương tiện</option>
                        <option value="OTHER">📦 Khác</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Số lượng</label>
                    <input type="number" name="items[${itemIndex}][qty]" 
                        class="form-control item-qty text-center" 
                        min="1" value="1" oninput="updateTotals()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Đơn giá (VNĐ)</label>
                    <input type="number" name="items[${itemIndex}][unit_price]" 
                        class="form-control item-price text-end" 
                        min="0" step="1000" value="0" oninput="updateTotals()"
                        placeholder="0">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger w-100" 
                        onclick="this.closest('.item-row').remove(); updateTotals();"
                        title="Xóa dịch vụ này">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
        container.appendChild(row);
        itemIndex++;

        // Focus vào input tên dịch vụ
        const newInput = row.querySelector('input[type="text"]');
        if (newInput) newInput.focus();
    }

    function updateTotals() {
        const adults = parseInt(document.getElementById('adults').value || 0);
        const children = parseInt(document.getElementById('children').value || 0);
        document.getElementById('total_people').value = adults + children;

        const priceAdult = parseFloat(document.getElementById('price_adult').value || 0);
        const priceChildren = parseFloat(document.getElementById('price_children').value || 0);
        const tourAmount = (adults * priceAdult) + (children * priceChildren);

        let itemsAmount = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty')?.value || 0);
            const price = parseFloat(row.querySelector('.item-price')?.value || 0);
            itemsAmount += qty * price;
        });

        document.getElementById('tour_amount').textContent = tourAmount.toLocaleString('vi-VN');
        document.getElementById('items_amount').textContent = itemsAmount.toLocaleString('vi-VN');
        document.getElementById('total_amount').textContent = (tourAmount + itemsAmount).toLocaleString('vi-VN');
    }

    document.getElementById('adults').addEventListener('input', updateTotals);
    document.getElementById('children').addEventListener('input', updateTotals);
    document.getElementById('price_adult').addEventListener('input', updateTotals);
    document.getElementById('price_children').addEventListener('input', updateTotals);

    updateTotals();
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">