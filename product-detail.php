<?php 
include 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sql = "SELECT sp.*, lk.ten_loai FROM san_pham sp LEFT JOIN loai_kinh lk ON sp.loai_kinh_id = lk.id WHERE sp.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo '<div class="container mt-5 pt-5 text-center" style="min-height: 50vh;">
            <h2 class="fw-bold text-danger">Không tìm thấy sản phẩm</h2>
            <p class="text-muted">Sản phẩm này có thể đã bị xóa hoặc không tồn tại.</p>
            <a href="shop.php" class="btn btn-primary rounded-pill mt-3 px-4 py-2">Quay lại cửa hàng</a>
          </div>';
    include 'includes/footer.php';
    exit;
}

// Xử lý ảnh
$img_src = !empty($product['hinh_anh']) ? $product['hinh_anh'] : 'no-image.jpg';
if (!filter_var($img_src, FILTER_VALIDATE_URL)) { 
    $img_src = 'image/' . $img_src; 
}

// Check if in cart
$cart_qty = isset($_SESSION['cart'][$id]) ? $_SESSION['cart'][$id]['qty'] : 0;
?>

<div class="container mt-5 mb-5 pt-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light p-3 rounded-3 shadow-sm mb-4">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-primary"><i class="bi bi-house-door"></i> Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="shop.php" class="text-decoration-none text-primary">Cửa hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['ten_sp']) ?></li>
        </ol>
    </nav>

    <div class="bg-white rounded-4 shadow-sm p-4 p-md-5">
        <div class="row align-items-center">
            <!-- Product Image -->
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="position-relative d-flex justify-content-center bg-light rounded-4 overflow-hidden border p-3">
                    <?php if ($product['gia_cu']): ?>
                        <span class="badge bg-danger position-absolute top-0 start-0 m-3 px-3 py-2 fs-6 shadow-sm"><i class="bi bi-tags"></i> Khuyến Mãi</span>
                    <?php endif; ?>
                    
                    <img src="<?= htmlspecialchars($img_src) ?>" class="img-fluid rounded zoom-hover" alt="<?= htmlspecialchars($product['ten_sp']) ?>" style="max-height: 450px; object-fit: contain;">
                </div>
            </div>
            
            <!-- Product Details -->
            <div class="col-lg-6 ps-lg-5">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm me-2"><?= htmlspecialchars($product['ten_loai'] ?? 'Mặc định') ?></span>
                    <span class="text-muted small">| Lượt xem: <?= number_format($product['luot_xem']) ?> <i class="bi bi-eye"></i></span>
                </div>
                
                <h1 class="fw-bold mb-3 text-dark fs-2 lh-base" style="font-family: 'Inter', sans-serif;">
                    <?= htmlspecialchars($product['ten_sp']) ?>
                </h1>
                
                <div class="price-box mb-4 bg-light d-inline-block px-4 py-3 rounded-4 border-start border-4 border-primary">
                    <?php if ($product['gia_cu']): ?>
                        <div class="text-muted text-decoration-line-through mb-1" style="font-size: 1.1rem;"><?= number_format($product['gia_cu'], 0, ',', '.') ?> VNĐ</div>
                    <?php endif; ?>
                    <h2 class="text-danger fw-bold mb-0 m-0 fs-1">
                        <?= number_format($product['gia'], 0, ',', '.') ?> <small class="fs-4">VNĐ</small>
                    </h2>
                </div>
                
                <div class="mb-4">
                    <h5 class="fw-bold fs-6 text-uppercase text-secondary mb-3"><i class="bi bi-info-circle me-1"></i> Thông tin mô tả</h5>
                    <p class="text-muted lh-lg" style="font-size: 1.05rem;">
                        <?= nl2br(htmlspecialchars($product['mo_ta'])) ?>
                    </p>
                </div>
                
                <hr class="mb-4">

                <!-- Form Tùy chọn cắt kính -->
                <form action="cart.php" method="POST" class="w-100 flex-grow-1">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                    
                    <div class="card border border-primary mb-4 shadow-sm bg-light">
                        <div class="card-header bg-white border-bottom">
                            <div class="form-check form-switch py-2">
                                <input class="form-check-input" type="checkbox" id="require_prescription" name="require_prescription" value="1" onchange="togglePrescription(this)" style="width: 2.5em; height: 1.25em;">
                                <label class="form-check-label fw-bold text-primary ms-2 fs-6" for="require_prescription">Tôi muốn Đặt Mài Tròng Khúc Xạ</label>
                            </div>
                        </div>
                        <div class="card-body" id="prescription_box" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Chọn Loại Tròng Kính</label>
                                <select name="lens_type" class="form-select border-primary-subtle shadow-none text-dark fw-medium">
                                    <option value="Tròng Chống Xước (Cơ bản) | +150.000đ">Tròng Phản Quang Chống Xước (Cơ bản) | +150.000đ</option>
                                    <option value="Tròng Chống Ánh Sáng Xanh | +300.000đ">Tròng Chống Ánh Sáng Xanh (Bảo vệ mắt) | +300.000đ</option>
                                    <option value="Tròng Đổi Màu Đi Nắng | +450.000đ">Tròng Đổi Màu Đi Nắng (Thời trang) | +450.000đ</option>
                                    <option value="Tròng Siêu Mỏng 1.67 | +800.000đ">Tròng Siêu Mỏng 1.67 (Cho độ cận cao) | +800.000đ</option>
                                </select>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6 border-end">
                                    <h6 class="text-secondary fw-bold border-bottom pb-2 mb-3">Mắt Phải (R)</h6>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Độ Cận/Viễn (SPH)</label>
                                        <select name="right_sph" class="form-select form-select-sm shadow-none">
                                            <option value="0.00">0.00</option>
                                            <option value="-0.50">-0.50 (Cận nhẹ)</option>
                                            <option value="-1.00">-1.00</option>
                                            <option value="-1.50">-1.50</option>
                                            <option value="-2.00">-2.00</option>
                                            <option value="-2.50">-2.50</option>
                                            <option value="-3.00">-3.00</option>
                                            <option value="-3.50">-3.50</option>
                                            <option value="-4.00">-4.00</option>
                                            <option value="-4.50">-4.50</option>
                                            <option value="-5.00">-5.00</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Độ Loạn (CYL)</label>
                                        <select name="right_cyl" class="form-select form-select-sm shadow-none">
                                            <option value="0.00">Không Loạn</option>
                                            <option value="-0.50">-0.50</option>
                                            <option value="-1.00">-1.00</option>
                                            <option value="-1.50">-1.50</option>
                                            <option value="-2.00">-2.00</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Trục AXIS (Nếu loạn)</label>
                                        <input type="number" name="right_axis" class="form-control form-control-sm shadow-none" placeholder="VD: 180" min="0" max="180">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-secondary fw-bold border-bottom pb-2 mb-3">Mắt Trái (L)</h6>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Độ Cận/Viễn (SPH)</label>
                                        <select name="left_sph" class="form-select form-select-sm shadow-none">
                                            <option value="0.00">0.00</option>
                                            <option value="-0.50">-0.50 (Cận nhẹ)</option>
                                            <option value="-1.00">-1.00</option>
                                            <option value="-1.50">-1.50</option>
                                            <option value="-2.00">-2.00</option>
                                            <option value="-2.50">-2.50</option>
                                            <option value="-3.00">-3.00</option>
                                            <option value="-3.50">-3.50</option>
                                            <option value="-4.00">-4.00</option>
                                            <option value="-4.50">-4.50</option>
                                            <option value="-5.00">-5.00</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Độ Loạn (CYL)</label>
                                        <select name="left_cyl" class="form-select form-select-sm shadow-none">
                                            <option value="0.00">Không Loạn</option>
                                            <option value="-0.50">-0.50</option>
                                            <option value="-1.00">-1.00</option>
                                            <option value="-1.50">-1.50</option>
                                            <option value="-2.00">-2.00</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Trục AXIS (Nếu loạn)</label>
                                        <input type="number" name="left_axis" class="form-control form-control-sm shadow-none" placeholder="VD: 180" min="0" max="180">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 bg-white p-3 border rounded text-center">
                                <label class="form-label fw-bold small text-secondary">KHOẢNG CÁCH ĐỒNG TỬ (PD - mm)</label>
                                <input type="number" name="pd" class="form-control border-primary shadow-sm w-50 mx-auto text-center" style="font-weight: bold; font-size: 1.1rem; letter-spacing: 2px;" placeholder="Ví dụ: 62">
                                <div class="form-text mt-2" style="font-size: 0.82rem;">(Vui lòng điền từ giấy đo thị lực để tâm kính được khớp nhất)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-danger btn-lg w-100 rounded-pill py-3 px-4 fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2 hover-lift">
                            <i class="bi bi-cart-plus fs-4"></i>
                            <span style="font-size: 1.1rem;">Thêm vào Giỏ hàng</span>
                        </button>
                    </div>
                </form>

                <script>
                function togglePrescription(checkbox) {
                    var box = document.getElementById('prescription_box');
                    if(checkbox.checked) {
                        box.style.display = 'block';
                    } else {
                        box.style.display = 'none';
                    }
                }
                </script>

                <?php if ($cart_qty > 0): ?>
                    <div class="alert alert-success mt-3 py-2 d-inline-block border-0 rounded-pill shadow-sm pe-4">
                        <i class="bi bi-check-circle-fill fs-5 mx-2 text-success"></i>
                        <span class="fw-medium">Đã có <b><?= $cart_qty ?></b> sản phẩm trong giỏ!</span>
                        <a href="cart.php" class="ms-2 fw-bold text-success text-decoration-underline">Xem ngay</a>
                    </div>
                <?php endif; ?>

                <!-- Commitments -->
                <div class="row g-3 mt-4 pt-3 border-top">
                    <div class="col-sm-6 d-flex align-items-center">
                        <i class="bi bi-truck fs-3 text-primary me-3"></i>
                        <div>
                            <span class="fw-bold d-block fs-6">Giao hàng tận nơi</span>
                            <small class="text-muted">Miễn phí ship toàn quốc</small>
                        </div>
                    </div>
                    <div class="col-sm-6 d-flex align-items-center">
                        <i class="bi bi-shield-check fs-3 text-primary me-3"></i>
                        <div>
                            <span class="fw-bold d-block fs-6">Bảo hành 12 tháng</span>
                            <small class="text-muted">Chính hãng 100%</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.zoom-hover { transition: transform 0.4s ease; }
.zoom-hover:hover { transform: scale(1.08); }
.hover-lift { transition: all 0.2s ease; }
.hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(220, 53, 69, 0.3) !important; filter: brightness(1.1); }
</style>

<?php include 'includes/footer.php'; ?>
