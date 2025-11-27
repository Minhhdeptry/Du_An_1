<?php
// ====================== LOAD DATA ======================
$file = "tour.json";

if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
}

$tours = json_decode(file_get_contents($file), true);

// ====================== SAVE FUNCTION ======================
function saveData($data, $file) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// ====================== ADD ======================
if (isset($_POST['add'])) {
    $tours[] = [
        "id" => time(),
        "name" => $_POST['name'],
        "price" => $_POST['price'],
        "date" => $_POST['date']
    ];
    saveData($tours, $file);
    header("Location: tour.php");
    exit;
}

// ====================== DELETE ======================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $tours = array_filter($tours, fn($t) => $t['id'] != $id);
    saveData($tours, $file);
    header("Location: tour.php");
    exit;
}

// ====================== GET EDIT DATA ======================
$editTour = null;
if (isset($_GET['edit'])) {
    foreach ($tours as $t) {
        if ($t['id'] == $_GET['edit']) {
            $editTour = $t;
        }
    }
}

// ====================== UPDATE ======================
if (isset($_POST['update'])) {
    foreach ($tours as &$t) {
        if ($t['id'] == $_POST['id']) {
            $t['name'] = $_POST['name'];
            $t['price'] = $_POST['price'];
            $t['date'] = $_POST['date'];
        }
    }
    saveData($tours, $file);
    header("Location: tour.php");
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý Tour (No Database)</title>
</head>
<body>

<h1>Quản lý Tour </h1>

<!-- FORM ADD / EDIT -->
<?php if ($editTour == null): ?>
    <h2>Thêm Tour</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Tên tour" required>
        <input type="number" name="price" placeholder="Giá" required>
        <input type="date" name="date" required>
        <button name="add">Thêm</button>
    </form>

<?php else: ?>
    <h2>Sửa Tour</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $editTour['id'] ?>">
        <input type="text" name="name" value="<?= $editTour['name'] ?>" required>
        <input type="number" name="price" value="<?= $editTour['price'] ?>" required>
        <input type="date" name="date" value="<?= $editTour['date'] ?>" required>
        <button name="update">Cập nhật</button>
    </form>
<?php endif; ?>

<hr>

<h2>Danh sách Tour</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Tên Tour</th>
        <th>Giá</th>
        <th>Ngày</th>
        <th>Hành động</th>
    </tr>

    <?php foreach ($tours as $t): ?>
    <tr>
        <td><?= $t['id'] ?></td>
        <td><?= $t['name'] ?></td>
        <td><?= $t['price'] ?></td>
        <td><?= $t['date'] ?></td>
        <td>
            <a href="tour.php?edit=<?= $t['id'] ?>">Sửa</a> |
            <a href="tour.php?delete=<?= $t['id'] ?>" onclick="return confirm('Xóa?')">Xóa</a>
        </td>
    </tr>
    <?php endforeach; ?>

</table>

</body>
</html>
