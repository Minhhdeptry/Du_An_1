<!-- Code giao diện Dashboard của admin -->
<!-- <h1>Dashboard admin</h1> -->
 <!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TourManager Pro - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
        }

        /* Header */
        .header {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            background: #4285f4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .dropdown {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            font-size: 14px;
        }

        .user-avatar {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background: #4285f4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: #1a1a1a;
        }

        .user-role {
            font-size: 12px;
            color: #666;
        }

        /* Layout */
        .container {
            display: flex;
            height: calc(100vh - 70px);
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            background: white;
            padding: 20px 0;
            box-shadow: 2px 0 4px rgba(0,0,0,0.05);
        }

        .menu-item {
            padding: 12px 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.3s;
            color: #666;
            font-size: 15px;
            text-decoration: none;
            border-left: 3px solid transparent;
        }

        .menu-item:hover {
            background: #f8f9fa;
            color: #1a1a1a;
        }

        .menu-item.active {
            background: #4285f4;
            color: white;
            border-left-color: #1a73e8;
        }

        .menu-icon {
            font-size: 18px;
            width: 20px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 30px;
        }

        /* Dashboard Cards */
        .dashboard-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .stat-card {
            border-radius: 12px;
            padding: 30px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .card-blue {
            background: linear-gradient(135deg, #4285f4 0%, #5b9cff 100%);
        }

        .card-green {
            background: linear-gradient(135deg, #22c55e 0%, #34d399 100%);
        }

        .card-purple {
            background: linear-gradient(135deg, #a855f7 0%, #c084fc 100%);
        }

        .card-orange {
            background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 48px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .card-icon {
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 60px;
            opacity: 0.3;
            z-index: 0;
        }

        /* Icons */
        .icon-box { display: inline-block; }
        .icon-dashboard::before { content: "🏠"; }
        .icon-tour::before { content: "📦"; }
        .icon-booking::before { content: "📄"; }
        .icon-guide::before { content: "👤"; }
        .icon-schedule::before { content: "📅"; }
        .icon-report::before { content: "📊"; }
        .icon-package::before { content: "📦"; }
        .icon-users::before { content: "👥"; }
        .icon-user::before { content: "👤"; }
        .icon-chart::before { content: "📈"; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <div class="logo-icon">📍</div>
            <span>TourManager Pro</span>
        </div>
        <div class="user-section">
            <select class="dropdown">
                <option>Admin</option>
                
            </select>
            <div class="user-avatar">
                <div class="avatar">A</div>
                <div class="user-info">
                    <div class="user-name">Admin User</div>
                    <div class="user-role">Quản trị viên</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="menu-item active">
                <span class="menu-icon icon-dashboard"></span>
                <span>Dashboard</span>
            </div>
            <div class="menu-item">
                <span class="menu-icon icon-tour"></span>
                <span>Quản lý Danh Sách Tour</span>
            </div>
            <div class="menu-item">
                <span class="menu-icon icon-booking"></span>
                <span>Booking</span>
            </div>
            <div class="menu-item">
                <span class="menu-icon icon-guide"></span>
                <span>Hướng dẫn viên</span>
            </div>
            <div class="menu-item">
                <span class="menu-icon icon-schedule"></span>
                <span>Lịch điều hành</span>
            </div>
            <div class="menu-item">
                <span class="menu-icon icon-report"></span>
                <span>Báo cáo</span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1 class="page-title">Quản lý Tour</h1>
            
            <div class="dashboard-grid">
                <!-- Tổng Tour -->
                <div class="stat-card card-blue">
                    <div class="stat-label">Tổng Tour</div>
                    <div class="stat-value">24</div>
                    <div class="card-icon icon-package"></div>
                </div>

                <!-- Booking -->
                <div class="stat-card card-green">
                    <div class="stat-label">Booking</div>
                    <div class="stat-value">156</div>
                    <div class="card-icon icon-users"></div>
                </div>

                <!-- Hướng Dẫn Viên -->
                <div class="stat-card card-purple">
                    <div class="stat-label">Hướng Dẫn Viên</div>
                    <div class="stat-value">18</div>
                    <div class="card-icon icon-user"></div>
                </div>

                <!-- Doanh Thu Tháng -->
                <div class="stat-card card-orange">
                    <div class="stat-label">Doanh Thu Tháng</div>
                    <div class="stat-value">2.4B</div>
                    <div class="card-icon icon-chart"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<button type="button" name="" id="" class="btn btn-primary" btn-lg btn-block>12</button>