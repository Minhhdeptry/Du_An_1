<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Dashboard' ?></title>

    <!-- Font & Icon -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="assets/css/hdv-dashboard.css"> -->
    <style>
        /* RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
        }

        :root {
            --header-height: 70px;
            --sidebar-width: 230px;
        }

        /* HEADER */
        .header {
            background: #fff;
            height: var(--header-height);
            padding: 0 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 999;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 22px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .logo-icon {
            width: 34px;
            height: 34px;
            background: #4285f4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
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
        }

        .user-role {
            font-size: 12px;
            color: #666;
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-width);
            background: #fff;
            border-right: 1px solid #ddd;
            position: fixed;
            top: var(--header-height);
            left: 0;
            bottom: 0;
            overflow-y: auto;
            padding-top: 10px;
        }

        .menu-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            color: #555;
            font-size: 15px;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: 0.25s;
        }

        .menu-item:hover {
            background: #f2f4f7;
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

        /* MAIN CONTENT */
        .admin-wrapper {
            display: flex;
        }

        .admin-content {
            margin-top: var(--header-height);
            margin-left: var(--sidebar-width);
            padding: 30px;
            width: calc(100% - var(--sidebar-width));
            min-height: calc(100vh - var(--header-height));
        }

        .page-title {
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 25px;
        }

        /* DASHBOARD CARDS */
        .stat-card {
            border-radius: 12px;
            padding: 28px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .stat-value {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-label {
            opacity: 0.85;
        }

        .card-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 55px;
            opacity: 0.25;
        }

        .card-blue {
            background: linear-gradient(135deg, #4285f4, #5b9cff);
        }

        .card-green {
            background: linear-gradient(135deg, #22c55e, #34d399);
        }

        .card-orange {
            background: linear-gradient(135deg, #f97316, #fb923c);
        }

        .card-purple {
            background: linear-gradient(135deg, #a855f7, #c084fc);
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <?php include "./views/layout/adminHeader.php"; ?>

    <div class="admin-wrapper">

        <!-- SIDEBAR -->
        <?php include "./views/layout/adminSidebar.php"; ?>

        <!-- CONTENT -->
        <div class="admin-content">
            <?php include $view; ?>
        </div>

    </div>

</body>

</html>
