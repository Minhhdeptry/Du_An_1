<div class="container mt-4">
    <h2>Thêm danh mục tour</h2>

    <form action="index.php?act=admin-category-store" method="POST" class="mt-3">

        <div class="form-group">
            <label>Mã danh mục</label>
            <input type="text" name="code" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Tên danh mục</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Ghi chú</label>
            <textarea name="note" class="form-control" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label>Trạng thái</label>
            <select name="is_active" class="form-control">
                <option value="1">Hiển thị</option>
                <option value="0">Ẩn</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Lưu</button>
        <a href="index.php?act=admin-category" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
