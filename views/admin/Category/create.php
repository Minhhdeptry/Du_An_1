<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title">➕ Thêm Danh mục Tour</h2>
        <a href="index.php?act=admin-category" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="index.php?act=admin-category-store" method="POST">

                <div class="mb-3">
                    <label for="code" class="form-label">Mã danh mục</label>
                    <input type="text" class="form-control" id="code" name="code" required>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Tên danh mục</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="mb-3">
                    <label for="note" class="form-label">Ghi chú</label>
                    <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label for="is_active" class="form-label">Trạng thái</label>
                    <select class="form-select" id="is_active" name="is_active">
                        <option value="1" selected>Hiển thị</option>
                        <option value="0">Ẩn</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Thêm mới
                </button>
                <a href="index.php?act=admin-category" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Hủy
                </a>

            </form>
        </div>
    </div>
</div>