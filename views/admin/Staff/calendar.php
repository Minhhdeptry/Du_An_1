<?php
// ============================================
// FILE: views/admin/Staff/calendar.php
// ============================================
?>

<style>
    /* CALENDAR CONTAINER */
    .calendar-wrapper {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    /* HEADER */
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e5e7eb;
    }

    .calendar-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
    }

    .calendar-nav {
        display: flex;
        gap: 8px;
    }

    .btn-nav {
        padding: 8px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: white;
        color: #374151;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-nav:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .btn-nav.btn-today {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .btn-nav.btn-today:hover {
        background: #2563eb;
    }

    /* CALENDAR GRID */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        background: #e5e7eb;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }

    .calendar-day-header {
        background: #1f2937;
        color: white;
        padding: 12px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
    }

    .calendar-day {
        background: white;
        min-height: 120px;
        padding: 8px;
        position: relative;
        transition: all 0.2s;
    }

    .calendar-day:hover {
        background: #f9fafb;
    }

    .calendar-day-number {
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
        font-size: 15px;
    }

    .calendar-day.other-month {
        background: #f9fafb;
        opacity: 0.4;
    }

    .calendar-day.today {
        background: #fef3c7;
        border: 2px solid #f59e0b;
    }

    .calendar-day.today .calendar-day-number {
        color: #d97706;
        font-weight: 700;
    }

    /* EVENT STYLES */
    .calendar-event {
        padding: 6px 8px;
        border-radius: 6px;
        font-size: 11px;
        margin-bottom: 4px;
        cursor: pointer;
        transition: all 0.2s;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        position: relative;
        font-weight: 500;
    }

    .calendar-event:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10;
    }

    /* Event Colors */
    .calendar-event.guide {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-left: 3px solid #047857;
    }

    .calendar-event.assistant {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-left: 3px solid #b45309;
    }

    /* Tooltip */
    .calendar-event::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #1f2937;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
        margin-bottom: 5px;
        z-index: 1000;
    }

    .calendar-event:hover::after {
        opacity: 1;
    }

    /* LEGEND */
    .calendar-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        margin-top: 20px;
        padding: 16px;
        background: #f9fafb;
        border-radius: 10px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 500;
    }

    .legend-color {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .legend-color.guide {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .legend-color.assistant {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .legend-color.today {
        background: #fef3c7;
        border: 2px solid #f59e0b;
    }

    /* STATS CARDS */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-top: 24px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        text-align: center;
        border-left: 4px solid;
        transition: all 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card.primary {
        border-color: #3b82f6;
    }

    .stat-card.success {
        border-color: #10b981;
    }

    .stat-card.warning {
        border-color: #f59e0b;
    }

    .stat-card.info {
        border-color: #06b6d4;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .calendar-day {
            min-height: 80px;
            padding: 4px;
        }

        .calendar-event {
            font-size: 9px;
            padding: 4px 6px;
        }

        .calendar-day-number {
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid mt-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">📅 Lịch làm việc HDV</h1>
            <p class="text-muted mb-0">Quản lý phân công hướng dẫn viên theo lịch trình</p>
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
                        <?php for ($y = date('Y') - 10; $y <= date('Y') + 0; $y++): ?>
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
    <div class="calendar-wrapper">
        <div class="calendar-header">
            <div class="calendar-title">
                Tháng <?= $month ?>/<?= $year ?>
            </div>
            <div class="calendar-nav">
                <a href="?act=admin-staff-calendar&month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?><?= !empty($_GET['staff_id']) ? '&staff_id=' . $_GET['staff_id'] : '' ?>"
                    class="btn-nav">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <a href="?act=admin-staff-calendar&month=<?= date('m') ?>&year=<?= date('Y') ?><?= !empty($_GET['staff_id']) ? '&staff_id=' . $_GET['staff_id'] : '' ?>"
                    class="btn-nav btn-today">
                    Hôm nay
                </a>
                <a href="?act=admin-staff-calendar&month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?><?= !empty($_GET['staff_id']) ? '&staff_id=' . $_GET['staff_id'] : '' ?>"
                    class="btn-nav">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        <!-- Legend -->
        <div class="calendar-legend">
            <div class="legend-item">
                <div class="legend-color guide"></div>
                <span>HDV chính</span>
            </div>
            <div class="legend-item">
                <div class="legend-color assistant"></div>
                <span>HDV phụ</span>
            </div>
            <div class="legend-item">
                <div class="legend-color today"></div>
                <span>Hôm nay</span>
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
            $firstDay = date('N', strtotime("$year-$month-01")) % 7;
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
                        $tourCode = htmlspecialchars($event['tour_code'] ?? 'N/A');
                        $staffName = htmlspecialchars($event['staff_name'] ?? 'N/A');
                        $roleName = $event['role'] === 'GUIDE' ? 'HDV chính' : 'HDV phụ';

                        $tooltip = "$tourCode - $staffName ($roleName)";

                        echo '<div class="calendar-event ' . $roleClass . '" data-tooltip="' . $tooltip . '">';
                        echo $tourCode;
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


    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-value"><?= count($schedules) ?></div>
            <div class="stat-label">Tổng lịch trình</div>
        </div>
        <div class="stat-card success">
            <div class="stat-value"><?= count(array_unique(array_column($schedules, 'staff_id'))) ?></div>
            <div class="stat-label">HDV tham gia</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-value"><?= count(array_filter($schedules, fn($s) => $s['role'] === 'GUIDE')) ?></div>
            <div class="stat-label">HDV chính</div>
        </div>
        <div class="stat-card info">
            <div class="stat-value"><?= count(array_filter($schedules, fn($s) => $s['role'] === 'ASSISTANT')) ?></div>
            <div class="stat-label">HDV phụ</div>
        </div>
    </div>

</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">