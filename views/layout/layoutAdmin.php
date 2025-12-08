<?php
// Đảm bảo include định nghĩa block trước
require_once __DIR__ . '/block/block.php';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<title><?= $title ?></title>

<body>
    <?php block('header') ?>
    <?php block('aside') ?>
    <!-- CONTENT -->
    <div class="content-wrapper">
       <?php include $view; ?>

    </div>


    <style>
        .content-wrapper {
            margin-top: 50px;
            margin-left:
                <?= ($_SESSION['user']['role'] == "admin") ? '250px' : '50px'; ?>
            ;
            min-height: 100vh;
        }
    </style>
</body>