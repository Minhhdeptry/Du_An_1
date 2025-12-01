<?php
// Kiểm tra bắt buộc có $category
if (!isset($category) || !$category) {
    header("Location: index.php?act=admin-category");
    exit;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Sửa Danh mục</h2>
        <a href="index.php?act=admin-category" class="btn btn-secondary btn-sm">Quay lại</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="index.php?act=admin-category-update" method="POST">
                <input type="hidden" name="id" value="<?= $category['id'] ?>">

                <div class="form-group mb-3">
                    <label for="code">Mã danh mục</label>
                    <input 
                        id="code"
                        name="code" 
                        class="form-control" 
                        value="<?= htmlspecialchars($category['code'] ?? '') ?>" 
                        required
                    >
                </div>

                <div class="form-group mb-3">
                    <label for="name">Tên danh mục</label>
                    <input 
                        id="name"
                        name="name" 
                        class="form-control" 
                        value="<?= htmlspecialchars($category['name'] ?? '') ?>" 
                        required
                    >
                </div>

                <div class="form-group mb-3">
                    <label for="note">Ghi chú</label>
                    <textarea 
                        id="note"
                        name="note" 
                        class="form-control" 
                        rows="3"
                    ><?= htmlspecialchars($category['note'] ?? '') ?></textarea>
                </div>

                <div class="form-group mb-3">
                    <label for="is_active">Trạng thái</label>
                    <select name="is_active" id="is_active" class="form-control">
                        <option value="1" <?= $category['is_active'] ? 'selected' : '' ?>>
                            Hiển thị
                        </option>
                        <option value="0" <?= !$category['is_active'] ? 'selected' : '' ?>>
                            Ẩn
                        </option>
                    </select>
                </div>

                <button class="btn btn-primary">Cập nhật</button>
                <a href="index.php?act=admin-category" class="btn btn-outline-secondary">Hủy</a>
            </form>

        </div>
    </div>
</div>
