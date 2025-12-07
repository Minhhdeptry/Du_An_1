<div class="container mt-4">
    <h2 class="mb-4">
        <i class="bi bi-calendar-week text-primary"></i>
        Chọn Tour để quản lý lịch trình
    </h2>

    <!-- Form tìm kiếm -->
    <form class="row g-2 mb-4" method="get" action="index.php">
        <input type="hidden" name="act" value="admin-itinerary-list">
        <div class="col-auto">
            <input type="text" name="keyword" class="form-control" placeholder="Tìm tour theo tên, mã..."
                value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">
                <i class="bi bi-search"></i> Tìm kiếm
            </button>
        </div>
        <?php if (!empty($_GET['keyword'])): ?>
            <div class="col-auto">
                <a href="index.php?act=admin-itinerary-list" class="btn btn-secondary">Xóa</a>
            </div>
        <?php endif; ?>
    </form>

    <!-- Danh sách tour -->
    <?php if (empty($tours)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Chưa có tour nào. Hãy tạo tour trước!
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($tours as $tour): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card shadow-sm h-100">
                        <?php if (!empty($tour['image_url'])): ?>
                            <?php
                            // ✅ XỬ LÝ ĐƯỜNG DẪN ẢNH
                            $image = trim($tour['image_url']);

                            // Nếu đã có đường dẫn đầy đủ (http hoặc /)
                            if (preg_match('#^(https?:)?//#i', $image) || str_starts_with($image, '/')) {
                                $imageSrc = $image;
                            }
                            // Nếu đã có assets/ trong tên
                            elseif (str_contains($image, 'assets/')) {
                                $imageSrc = $image;
                            }
                            // Nếu chỉ là tên file → thêm assets/images/
                            else {
                                $imageSrc = 'assets/images/' . $image;
                            }
                            ?>
                            <img src="<?= htmlspecialchars($imageSrc) ?>" class="card-img-top"
                                style="height: 180px; object-fit: cover;" alt="<?= htmlspecialchars($tour['title']) ?>"
                                onerror="this.src='assets/images/placeholder.jpg'">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($tour['title']) ?></h5>
                            <p class="text-muted mb-2">
                                <small>
                                    <i class="bi bi-tag"></i> <?= htmlspecialchars($tour['code']) ?> |
                                    <i class="bi bi-clock"></i> <?= $tour['duration_days'] ?> ngày
                                </small>
                            </p>
                            <div class="mt-auto">
                                <a href="index.php?act=admin-itinerary&tour_id=<?= $tour['id'] ?>"
                                    class="btn btn-primary w-100">
                                    <i class="bi bi-calendar-week"></i> Quản lý lịch trình
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">