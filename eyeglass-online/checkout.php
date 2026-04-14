<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM khach_hang WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Tính tổng tiền để hiển thị
$product_ids = [];
foreach ($_SESSION['cart'] as $item) {
    $product_ids[$item['id']] = $item['id'];
}
$ids_string = implode(',', array_map('intval', $product_ids));
$db_products = [];
$result = $conn->query("SELECT id, ten_sp, gia, hinh_anh FROM san_pham WHERE id IN ($ids_string)");
while ($row = $result->fetch_assoc()) {
    $db_products[$row['id']] = $row;
}

$tong_tien = 0;
$cart_details = [];
foreach ($_SESSION['cart'] as $cart_key => $item) {
    if (!isset($db_products[$item['id']])) continue;
    
    $lens_price = 0;
    $lens_name = "Kính nguyên bản";
    if ($item['options'] != null) {
        $lens_name = $item['options']['lens_type'];
        if (preg_match('/(?:\+|)(\d{1,3}(?:\.\d{3})*)đ/u', $lens_name, $matches)) {
            $lens_price = str_replace('.', '', $matches[1]);
        }
    }
    
    $unit_price = $db_products[$item['id']]['gia'] + $lens_price;
    $subtotal = $unit_price * $item['qty'];
    $tong_tien += $subtotal;

    $cart_details[] = [
        'ten_sp' => $db_products[$item['id']]['ten_sp'],
        'qty' => $item['qty'],
        'subtotal' => $subtotal,
        'options' => $item['options']
    ];
}

// Xử lý Form Đặt Hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $ten_nguoi_nhan = trim($_POST['ten_nguoi_nhan']);
    $sdt_nguoi_nhan = trim($_POST['sdt_nguoi_nhan']);
    $dia_chi = trim($_POST['dia_chi_giao_hang']);
    $ghi_chu = trim($_POST['ghi_chu'] ?? '');
    $pt_thanh_toan = $_POST['phuong_thuc_thanh_toan'];

    // Insert don_hang
    $stmt = $conn->prepare("INSERT INTO don_hang (khach_hang_id, ten_nguoi_nhan, sdt_nguoi_nhan, dia_chi_giao_hang, ghi_chu, phuong_thuc_thanh_toan, tong_tien) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssd", $user_id, $ten_nguoi_nhan, $sdt_nguoi_nhan, $dia_chi, $ghi_chu, $pt_thanh_toan, $tong_tien);
    
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        // Insert chi_tiet_don_hang thiết lập Tùy chọn độ cận (tuy_chon JSON)
        $stmt_detail = $conn->prepare("INSERT INTO chi_tiet_don_hang (don_hang_id, san_pham_id, so_luong, gia_ban, tuy_chon) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($_SESSION['cart'] as $item) {
            if (!isset($db_products[$item['id']])) continue;
            
            $lens_price = 0;
            if ($item['options'] != null) {
                $lens_name = $item['options']['lens_type'];
                if (preg_match('/(?:\+|)(\d{1,3}(?:\.\d{3})*)đ/u', $lens_name, $matches)) {
                    $lens_price = str_replace('.', '', $matches[1]);
                }
            }
            $gia_ban = $db_products[$item['id']]['gia'] + $lens_price;
            $sp_id = $item['id'];
            $sl = $item['qty'];
            
            // Xử lý tùy chọn thành dạng JSON
            $tuy_chon_json = $item['options'] ? json_encode($item['options'], JSON_UNESCAPED_UNICODE) : null;
            
            $stmt_detail->bind_param("iiids", $order_id, $sp_id, $sl, $gia_ban, $tuy_chon_json);
            $stmt_detail->execute();
        }

        // Làm trống giỏ hàng và chuyển hướng
        $_SESSION['cart'] = [];
        header("Location: order-success.php?id=" . $order_id);
        exit;
    }
}
?>

<div class="container mt-5 mb-5" style="min-height: 60vh;">
    <h2 class="fw-bold mb-4">Thanh Toán & Đặt Hàng <i class="bi bi-credit-card-2-front"></i></h2>
    
    <form method="POST" action="checkout.php" class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <h5 class="fw-bold mb-4 border-bottom pb-3"><i class="bi bi-geo-alt text-danger me-2"></i> Thông tin Người nhận hàng</h5>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Họ và Tên</label>
                        <input type="text" name="ten_nguoi_nhan" class="form-control" value="<?= htmlspecialchars($user['ho_ten'] ?? $user['username']) ?>" required placeholder="VD: Nguyễn Văn A">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Số điện thoại</label>
                        <input type="text" name="sdt_nguoi_nhan" class="form-control" value="<?= htmlspecialchars($user['sdt'] ?? '') ?>" required placeholder="VD: 0987654321">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Địa chỉ Giao hàng chi tiết</label>
                        <textarea name="dia_chi_giao_hang" class="form-control" rows="3" required placeholder="Ghi rõ Số nhà, Đường, Phường/Xã, Quận/Huyện, Tỉnh/Thành"><?= htmlspecialchars($user['dia_chi'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Gợi ý / Ghi chú về Đơn kính (Tùy chọn)</label>
                        <textarea name="ghi_chu" class="form-control" rows="2" placeholder="Ví dụ: Vui lòng bọc kỹ, giao hàng vào buổi tối..."></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h5 class="fw-bold mb-4 border-bottom pb-3"><i class="bi bi-wallet2 text-success me-2"></i> Phương thức Thanh toán</h5>
                    
                    <div class="form-check border rounded p-3 mb-2 bg-light">
                        <input class="form-check-input ms-1 mt-2" type="radio" name="phuong_thuc_thanh_toan" id="cod" value="COD" checked>
                        <label class="form-check-label ms-2 fw-bold d-block" for="cod">
                            Thanh toán khi nhận hàng (COD)
                            <small class="d-block fw-normal text-muted mt-1">Quý khách sẽ thanh toán bằng tiền mặt khi Shipper giao hàng tới.</small>
                        </label>
                    </div>

                    <div class="form-check border rounded p-3 mb-2 bg-light opacity-75">
                        <input class="form-check-input ms-1 mt-2" type="radio" name="phuong_thuc_thanh_toan" id="bank" value="Chuyển khoản" disabled>
                        <label class="form-check-label ms-2 fw-bold d-block" for="bank">
                            Chuyển khoản Ngân hàng (Đang bảo trì)
                            <small class="d-block fw-normal text-muted mt-1">Tính năng chuyển khoản tự động tạm thời đóng.</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột Giỏ Hàng -->
        <div class="col-lg-5 mt-4 mt-lg-0">
            <div class="card shadow-sm border-2 border-primary rounded-4 sticky-top" style="top: 100px;">
                <div class="card-body p-4 bg-primary bg-opacity-10 rounded-top-4">
                    <h5 class="fw-bold mb-0 text-primary">Sản phẩm trong Đơn (<?= count($cart_details) ?> món)</h5>
                </div>
                <div class="card-body p-4 bg-white rounded-bottom-4">
                    
                    <ul class="list-group list-group-flush mb-4">
                        <?php foreach($cart_details as $item): ?>
                            <li class="list-group-item px-0 py-3 border-bottom-dashed">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="me-auto overflow-hidden pe-3">
                                        <div class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($item['ten_sp']) ?>">
                                            <?= htmlspecialchars($item['ten_sp']) ?> <span class="badge bg-secondary">x<?= $item['qty'] ?></span>
                                        </div>
                                        <?php if(isset($item['options'])): 
                                            $opt = $item['options'];
                                            // Lấy tên Tròng bằng cách cắt bỏ phần giá đính kèm
                                            $short_lens = explode(' |', $opt['lens_type'])[0] ?? 'Tròng Cỡ Nhãn';
                                        ?>
                                            <div class="text-secondary" style="font-size: 0.8rem;">
                                                <i class="bi bi-clipboard2-check"></i> <b>Cắt:</b> <?= htmlspecialchars($short_lens) ?>
                                                <div class="mt-1 opacity-75">
                                                    [R: SPH <?= $opt['right_sph'] ?> CYL <?= $opt['right_cyl'] ?>] 
                                                    [L: SPH <?= $opt['left_sph'] ?> CYL <?= $opt['left_cyl'] ?>]
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-secondary small opacity-75">Khung Nguyên bản</div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-danger fw-bold ms-auto"><?= number_format($item['subtotal'], 0, ',', '.') ?>đ</span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span>Tạm tính:</span>
                        <span class="fw-medium text-dark"><?= number_format($tong_tien, 0, ',', '.') ?>đ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span>Phí giao hàng:</span>
                        <span class="text-success fw-semibold">Miễn phí ship</span>
                    </div>
                    
                    <hr class="my-4 border-primary">
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5 text-dark">Thành tiền:</span>
                        <span class="text-danger fw-bold fs-3"><?= number_format($tong_tien, 0, ',', '.') ?>đ</span>
                    </div>

                    <button type="submit" name="place_order" class="btn btn-danger w-100 rounded-pill py-3 fw-bold fs-5 shadow-sm text-uppercase d-flex shadow align-items-center justify-content-center gap-2 hover-raise">
                        <i class="bi bi-shield-check"></i> Xác Nhận Đặt Hàng
                    </button>
                    
                    <div class="text-center mt-3">
                        <a href="cart.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Quay lại giỏ hàng sửa lỗi</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.border-bottom-dashed { border-bottom: 1px dashed #dee2e6; }
.hover-raise { transition: all 0.25s ease; }
.hover-raise:hover { transform: translateY(-3px); filter: brightness(1.1); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>

<?php include 'includes/footer.php'; ?>
