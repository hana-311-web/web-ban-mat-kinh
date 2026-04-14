<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section text-center text-md-start">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-md-6 mb-5 mb-md-0">
                <span class="badge bg-primary rounded-pill px-3 py-2 mb-3 shadow-sm" style="font-size: 0.9rem;">Bộ Sưu Tập Mới 2026</span>
                <h1 class="hero-title">Định Hình<br><span class="text-primary">Phong Cách</span> Của Bạn</h1>
                <p class="hero-subtitle">Khám phá hàng trăm mẫu kính thời trang, bảo vệ mắt tối ưu cùng dịch vụ tư vấn online chuyên nghiệp ngay tại nhà.</p>
                <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                    <a href="shop.php" class="btn btn-primary btn-lg btn-custom shadow-sm"><i class="bi bi-cart3 me-2"></i>Mua Ngay</a>
                    <a href="shop.php" class="btn btn-outline-dark btn-lg btn-custom">Xem Chi Tiết</a>
                </div>
            </div>
            <div class="col-md-6">
                <!-- Using a high quality placeholder from Unsplash -->
                <img src="https://images.unsplash.com/photo-1577803645773-f96470509666?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Người mẫu đeo kính" class="img-fluid rounded-4 shadow-lg" style="transform: rotate(-3deg); transition: transform 0.3s; border: 5px solid white;" onmouseover="this.style.transform='rotate(0deg)'" onmouseout="this.style.transform='rotate(-3deg)'">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon">🚀</div>
                    <h5>Giao Hàng Siêu Tốc</h5>
                    <p class="text-muted mb-0">Miễn phí giao hàng cho đơn hàng từ 500k. Nhận hàng sau 2H tại nội thành.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon">✨</div>
                    <h5>Dùng Thử Tại Nhà</h5>
                    <p class="text-muted mb-0">Thoải mái thử nghiệm lên đến 3 mẫu kính tại nhà bạn mà không mất phí.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon">💎</div>
                    <h5>Chất Lượng Cao Cấp</h5>
                    <p class="text-muted mb-0">Cam kết hàng chính hãng 100%, bảo hành lỗi 1 đổi 1 trong vòng 30 ngày.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- New Products Section -->
<section class="py-5 bg-white rounded-top-5 mt-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h6 class="text-primary fw-bold text-uppercase mb-1">Cập Nhật Tuần Này</h6>
                <h2 class="fw-bold mb-0" style="color: #2c3e50;">Sản Phẩm MỚI</h2>
            </div>
            <a href="shop.php" class="text-decoration-none fw-semibold">Xem tất cả &rarr;</a>
        </div>
        
        <div class="row g-4">
            <?php
            // Lấy 4 sản phẩm mới nhất
            if (isset($conn)) {
                try {
                    $stmt = $conn->prepare("SELECT * FROM san_pham WHERE trang_thai = 1 ORDER BY id DESC LIMIT 4");
                    if ($stmt && $stmt->execute()) {
                        $products_new = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    } else { $products_new = []; }
                } catch (Exception $e) { $products_new = []; }
            } else { $products_new = []; }

            foreach ($products_new as $p): 
                $img_src = !empty($p['hinh_anh']) ? $p['hinh_anh'] : 'https://placehold.co/400x400/ebebeb/a3a3a3?text=Kinhmatt';
                if (!filter_var($img_src, FILTER_VALIDATE_URL)) { $img_src = 'image/' . $img_src; }
            ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card product-card h-100 position-relative border-0 shadow-sm transition-hover">
                    <span class="badge bg-success badge-custom shadow-sm position-absolute top-0 start-0 m-2 px-2 py-1 z-1">MỚI</span>
                    
                    <a href="product-detail.php?id=<?= $p['id'] ?>" class="product-img-wrapper d-block overflow-hidden" style="height: 250px;">
                        <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($p['ten_sp']) ?>" class="w-100 h-100 object-fit-cover" style="transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    </a>
                    <div class="card-body text-center d-flex flex-column p-4">
                        <h5 class="product-title mb-2 text-truncate fw-bold fs-6" title="<?= htmlspecialchars($p['ten_sp']) ?>">
                            <?= htmlspecialchars($p['ten_sp']) ?>
                        </h5>
                        <p class="product-price mt-auto mb-3 text-danger fw-bold fs-5"><?= number_format($p['gia'], 0, ',', '.') ?>đ</p>
                        <a href="cart.php?action=add&id=<?= $p['id'] ?>" class="btn btn-outline-primary w-100 btn-custom mt-auto rounded-pill fw-semibold">
                            <i class="bi bi-cart-plus"></i> Thêm Giỏ Hàng
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Hot Products Section -->
<section class="py-5 bg-light mt-2">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h6 class="text-danger fw-bold text-uppercase mb-1"><i class="bi bi-fire"></i> Bán Chạy Nhất</h6>
                <h2 class="fw-bold mb-0" style="color: #2c3e50;">Sản Phẩm HOT</h2>
            </div>
            <a href="shop.php" class="text-decoration-none fw-semibold">Xem tất cả &rarr;</a>
        </div>
        
        <div class="row g-4">
            <?php
            // Lấy 4 sản phẩm HOT nhiều lượt xem nhất
            if (isset($conn)) {
                try {
                    $stmt = $conn->prepare("SELECT * FROM san_pham WHERE trang_thai = 1 ORDER BY luot_xem DESC, id DESC LIMIT 4");
                    if ($stmt && $stmt->execute()) {
                        $products_hot = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    } else { $products_hot = []; }
                } catch (Exception $e) { $products_hot = []; }
            } else { $products_hot = []; }

            foreach ($products_hot as $p): 
                $img_src = !empty($p['hinh_anh']) ? $p['hinh_anh'] : 'https://placehold.co/400x400/ebebeb/a3a3a3?text=Kinhmatt';
                if (!filter_var($img_src, FILTER_VALIDATE_URL)) { $img_src = 'image/' . $img_src; }
            ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card product-card h-100 position-relative border-0 shadow-sm transition-hover">
                    <span class="badge bg-danger badge-custom shadow-sm position-absolute top-0 start-0 m-2 px-2 py-1 z-1"><i class="bi bi-fire"></i> HOT</span>
                    
                    <a href="product-detail.php?id=<?= $p['id'] ?>" class="product-img-wrapper d-block overflow-hidden" style="height: 250px;">
                        <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($p['ten_sp']) ?>" class="w-100 h-100 object-fit-cover" style="transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    </a>
                    <div class="card-body text-center d-flex flex-column p-4">
                        <h5 class="product-title mb-2 text-truncate fw-bold fs-6" title="<?= htmlspecialchars($p['ten_sp']) ?>">
                            <?= htmlspecialchars($p['ten_sp']) ?>
                        </h5>
                        <p class="product-price mt-auto mb-3 text-danger fw-bold fs-5"><?= number_format($p['gia'], 0, ',', '.') ?>đ</p>
                        <a href="cart.php?action=add&id=<?= $p['id'] ?>" class="btn btn-outline-danger w-100 btn-custom mt-auto rounded-pill fw-semibold">
                            <i class="bi bi-cart-plus"></i> Thêm Giỏ Hàng
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.transition-hover { transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s; }
.transition-hover:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>

<!-- Newsletter / Call To Action -->
<section class="py-5 mb-5 mt-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9 text-center p-5 bg-dark text-white rounded-4 shadow-lg position-relative overflow-hidden" style="background: linear-gradient(45deg, #1e3c72, #2a5298) !important;">
                <div style="position: absolute; top: -50%; left: -10%; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                <div style="position: absolute; bottom: -50%; right: -10%; width: 400px; height: 400px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                
                <h3 class="fw-bold position-relative z-1 mb-3">Nhận Ưu Đãi Độc Quyền!</h3>
                <p class="position-relative z-1 mb-4 text-light" style="font-size: 1.1rem;">Đăng ký email để nhận ngay mã giảm giá <span class="fw-bold text-warning">15%</span> cho đơn hàng đầu tiên của bạn.</p>
                <form class="d-flex flex-column flex-sm-row justify-content-center gap-2 mx-auto position-relative z-1" style="max-width: 500px;">
                    <input type="email" class="form-control rounded-pill px-4 py-3" placeholder="Nhập địa chỉ email của bạn..." required>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 py-3 fw-bold text-dark shadow-sm">Đăng Ký Khuyến Mãi</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Thêm Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<?php include 'includes/footer.php'; ?>
