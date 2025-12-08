<?php
// ============================================
// FILE: views/admin/Staff/calendar.php
// ============================================
?>

<style>
    .calendar-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: #e5e7eb;
        border: 1px solid #e5e7eb;
    }
    
    .calendar-day-header {
        background: #f3f4f6;
        padding: 10px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
    }
    
    .calendar-day {
        background: white;
        min-height: 100px;
        padding: 8px;
        position: relative;
    }
    
    .calendar-day-number {
        font-weight: 600;
        color: #374151;
        margin-bottom: 5px;
    }
    
    .calendar-day.other-month {
        background: #f9fafb;
        opacity: 0.5;
    }
    
    .calendar-day.today {
        background: #fef3c7;
    }
    
    .calendar-event {
        background: #3b82f6;
        color: white;
        padding: 4px 6px;
        border-radius: 4px;
        font-size: 11px;
        margin-bottom: 3px;
        cursor: pointer;
        transition: all 0.2s;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .calendar-event:hover {
        background: #2563eb;
        transform: scale(1.02);
    }
    
    .calendar-event.guide {
        background: #10b981;
    }
    
    .calendar-event.assistant {
        background: #f59e0b;
    }
    
    .legend {
        display: flex;
        gap: 20px;
        margin-top: 15px;
        padding: 10px;
        background: #f9fafb;
        border-radius: 6px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }
    
    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
    }
</style>

<div class="container-fluid mt-4">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">📅 Lịch làm việc HDV</h2>
            <p class="text-muted mb-0">Xem lịch phân công hướng dẫn viên</p>
        </div>
        <a href="index.php?act=admin-staff" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="act" value="admin-staff-calendar">
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tháng</label>
                    <select name="month" class="form-select">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($month == $m) ? 'selected' : '' ?>>
                                Tháng <?= $m ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Năm</label>
                    <select name="year" class="form-select">
                        <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                            <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Lọc theo HDV</label>
                    <select name="staff_id" class="form-select">
                        <option value="">Tất cả HDV</option>
                        <?php foreach ($staffs as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($_GET['staff_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Calendar -->
    <div class="calendar-container">
        <div class="calendar-header">
            <h4 class="mb-0">Tháng <?= $month ?>/<?= $year ?></h4>
            <div class="btn-group">
                <a href="?act=admin-staff-calendar&month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?><?= !empty($_GET['staff_id']) ? '&staff_id=' . $_GET['staff_id'] : '' ?>" 
                   class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-chevron-left"></i> Tháng trước
                </a>
                <a href="?act=admin-staff-calendar&month=<?= date('m') ?>&year=<?= date('Y') ?><?= !empty($_GET['staff_id']) ? '&staff_id=' . $_GET['staff_id'] : '' ?>" 
                   class="btn btn-sm btn-outline-primary">
                    Hôm nay
                </a>
                <a href="?act=admin-staff-calendar&month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?><?= !empty($_GET['staff_id']) ? '&staff_id=' . $_GET['staff_id'] : '' ?>" 
                   class="btn btn-sm btn-outline-primary">
                    Tháng sau <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        <div class="calendar-grid">
            <!-- Day headers -->
            <?php 
            $days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
            foreach ($days as $day): 
            ?>
                <div class="calendar-day-header"><?= $day ?></div>
            <?php endforeach; ?>

            <!-- Calendar days -->
            <?php
            $firstDay = date('N', strtotime("$year-$month-01")) % 7; // 0 = Sunday
            $daysInMonth = date('t', strtotime("$year-$month-01"));
            $today = date('Y-m-d');
            
            // Group schedules by date
            $schedulesByDate = [];
            foreach ($schedules as $sch) {
                $start = new DateTime($sch['depart_date']);
                $end = new DateTime($sch['return_date']);
                $interval = new DateInterval('P1D');
                $daterange = new DatePeriod($start, $interval, $end->modify('+1 day'));
                
                foreach ($daterange as $date) {
                    $dateStr = $date->format('Y-m-d');
                    if (!isset($schedulesByDate[$dateStr])) {
                        $schedulesByDate[$dateStr] = [];
                    }
                    $schedulesByDate[$dateStr][] = $sch;
                }
            }
            
            // Previous month days
            $prevMonth = $month == 1 ? 12 : $month - 1;
            $prevYear = $month == 1 ? $year - 1 : $year;
            $daysInPrevMonth = date('t', strtotime("$prevYear-$prevMonth-01"));
            
            for ($i = $firstDay - 1; $i >= 0; $i--) {
                $day = $daysInPrevMonth - $i;
                echo '<div class="calendar-day other-month">';
                echo '<div class="calendar-day-number">' . $day . '</div>';
                echo '</div>';
            }
            
            // Current month days
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $isToday = ($dateStr === $today);
                $dayClass = 'calendar-day' . ($isToday ? ' today' : '');
                
                echo '<div class="' . $dayClass . '">';
                echo '<div class="calendar-day-number">' . $day . '</div>';
                
                // Events for this day
                if (isset($schedulesByDate[$dateStr])) {
                    foreach ($schedulesByDate[$dateStr] as $event) {
                        $roleClass = strtolower($event['role'] ?? '');
                        $title = htmlspecialchars($event['tour_code'] ?? 'N/A');
                        $staffName = htmlspecialchars($event['staff_name'] ?? 'N/A');
                        
                        echo '<div class="calendar-event ' . $roleClass . '" 
                                   title="' . $title . ' - ' . $staffName . ' (' . $event['role'] . ')">';
                        echo $title;
                        echo '</div>';
                    }
                }
                
                echo '</div>';
            }
            
            // Next month days
            $totalCells = $firstDay + $daysInMonth;
            $remainingCells = (7 - ($totalCells % 7)) % 7;
            
            for ($day = 1; $day <= $remainingCells; $day++) {
                echo '<div class="calendar-day other-month">';
                echo '<div class="calendar-day-number">' . $day . '</div>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- Legend -->
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #10b981;"></div>
                <span>HDV chính</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #f59e0b;"></div>
                <span>HDV phụ</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #fef3c7;"></div>
                <span>Hôm nay</span>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?= count($schedules) ?></h3>
                    <p class="mb-0 text-muted">Tổng lịch trình</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?= count(array_unique(array_column($schedules, 'staff_id'))) ?></h3>
                    <p class="mb-0 text-muted">HDV tham gia</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning"><?= count(array_filter($schedules, fn($s) => $s['role'] === 'GUIDE')) ?></h3>
                    <p class="mb-0 text-muted">HDV chính</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info"><?= count(array_filter($schedules, fn($s) => $s['role'] === 'ASSISTANT')) ?></h3>
                    <p class="mb-0 text-muted">HDV phụ</p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">