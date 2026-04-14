<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Khởi tạo session cho giỏ hàng
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --- LOGIC XỬ LÝ TRƯỚC KHI XUẤT HTML ---
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

// Làm sạch Session Cũ - Compatibility fix
foreach ($_SESSION['cart'] as $session_key => $item) {
    if (!isset($item['id'])) {
        $_SESSION['cart'] = []; // Xoá sạch giỏ
        header("Location: cart.php");
        exit;
    }
}

$options = null;
if (isset($_POST['require_prescription']) && $_POST['require_prescription'] == '1') {
    $options = [
        'lens_type' => $_POST['lens_type'] ?? '',
        'right_sph' => $_POST['right_sph'] ?? '',
        'right_cyl' => $_POST['right_cyl'] ?? '',
        'right_axis'=> $_POST['right_axis'] ?? '',
        'left_sph'  => $_POST['left_sph'] ?? '',
        'left_cyl'  => $_POST['left_cyl'] ?? '',
        'left_axis' => $_POST['left_axis'] ?? '',
        'pd'        => $_POST['pd'] ?? ''
    ];
}

function generateCartKey($id, $options) {
    if ($options === null) {
        return "prod_" . $id; 
    }
    return "prod_" . $id . "_" . md5(json_encode($options));
}

if ($action == 'add' && $id > 0) {
    $cart_key = generateCartKey($id, $options);
    
    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['qty'] += 1;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'id' => $id,
            'qty' => 1,
            'options' => $options
        ];
    }
    header("Location: cart.php");
    exit;
} 
elseif ($action == 'remove' && isset($_GET['key'])) {
    $key = $_GET['key'];
    if(isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    }
    header("Location: cart.php");
    exit;
} 
elseif ($action == 'clear') {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    if(isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $key => $qty) {
            $qty = (int)$qty;
            if ($qty > 0 && isset($_SESSION['cart'][$key])) {
                $_SESSION['cart'][$key]['qty'] = $qty;
            } elseif ($qty <= 0) {
                unset($_SESSION['cart'][$key]);
            }
        }
    }
    header("Location: cart.php");
    exit;
}

// Bắt đầu nhúng file Header giao diện (HTML)
include 'includes/header.php';

// Thu thập ID sản phẩm để Query Data
$product_ids = [];
foreach ($_SESSION['cart'] as $session_key => $item) {
    $product_ids[$item['id']] = $item['id'];
}

$db_products = [];
if (!empty($product_ids)) {
    $ids_string = implode(',', array_map('intval', $product_ids));
    $query = "SELECT id, ten_sp, hinh_anh, gia FROM san_pham WHERE id IN ($ids_string)";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $db_products[$row['id']] = $row;
    }
}

$total_price = 0;
?>

<div class="container mt-5 mb-5" style="min-height: 50vh;">
    <h2 class="fw-bold mb-4">Giỏ hàng của bạn <i class="bi bi-cart3"></i></h2>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-light text-center py-5 rounded-4 shadow-sm border">
            <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-cart-7359557-6024626.png" alt="Empty Cart" style="width: 200px; opacity: 0.8; margin-bottom: 20px;">
            <h4 class="fw-bold">Giỏ hàng hiện đang trống</h4>
            <p class="text-muted">Bạn chưa chọn mua sản phẩm nào. Hãy dạo một vòng cửa hàng nhé!</p>
            <a href="shop.php" class="btn btn-primary rounded-pill mt-2 px-5 py-2 shadow-sm font-weight-bold">Quay lại mua sắm ngay</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <form method="POST" action="cart.php">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr class="text-muted text-uppercase text-nowrap" style="font-size: 0.85rem;">
                                            <th class="ps-3 py-3">Sản phẩm & Tùy chọn</th>
                                            <th class="py-3">Đơn Giá</th>
                                            <th class="py-3" style="width: 120px;">Số lượng</th>
                                            <th class="py-3 text-end pe-4">Thành tiền</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        <?php foreach ($_SESSION['cart'] as $cart_key => $item): 
                                            if(!isset($db_products[$item['id']])) continue;
                                            
                                            $db_item = $db_products[$item['id']];
                                            $qty = $item['qty'];
                                            
                                            $lens_price = 0;
                                            $lens_name = "Kính nguyên bản";
                                            if ($item['options'] != null) {
                                                $lens_name = $item['options']['lens_type'];
                                                if (empty($lens_name)) $lens_name = "Tròng kính thường";
                                                
                                                if (preg_match('/(?:\+|)(\d{1,3}(?:\.\d{3})*)đ/u', $lens_name, $matches)) {
                                                    $lens_price = str_replace('.', '', $matches[1]);
                                                }
                                            }
                                            
                                            $unit_price = $db_item['gia'] + $lens_price;
                                            $subtotal = $unit_price * $qty;
                                            $total_price += $subtotal;

                                            $img_src = !empty($db_item['hinh_anh']) ? $db_item['hinh_anh'] : 'no-image.jpg';
                                            if (!filter_var($img_src, FILTER_VALIDATE_URL)) { 
                                                $img_src = 'image/' . $img_src; 
                                            }
                                        ?>
                                        <tr>
                                            <td class="ps-3 py-3">
                                                <div class="d-flex align-items-start">
                                                    <img src="<?= htmlspecialchars($img_src) ?>" class="rounded-3 me-3 object-fit-cover shadow-sm bg-light" style="width: 70px; height: 70px;">
                                                    <div>
                                                        <a href="product-detail.php?id=<?= $db_item['id'] ?>" class="text-decoration-none fw-bold text-dark fs-6 d-block mb-1"><?= htmlspecialchars($db_item['ten_sp']) ?></a>
                                                        
                                                        <?php if($item['options']): 
                                                            $opt = $item['options'];
                                                        ?>
                                                            <div class="bg-light p-2 rounded border border-primary-subtle mt-2" style="font-size: 0.8rem;">
                                                                <span class="badge bg-primary mb-1 text-wrap text-start lh-base d-inline-block"><i class="bi bi-magic"></i> <?= htmlspecialchars($lens_name) ?></span><br>
                                                                <table class="table table-sm table-borderless mb-0 mt-1">
                                                                    <tr>
                                                                        <td class="p-0 text-muted w-50"><b>R (Phải):</b> Cận <?= $opt['right_sph'] ?> | Loạn <?= $opt['right_cyl'] ?> <?= $opt['right_cyl'] != '0.00' ? '| AXIS ' . $opt['right_axis'] : '' ?></td>
                                                                        <td class="p-0 text-muted"><b>L (Trái):</b> Cận <?= $opt['left_sph'] ?> | Loạn <?= $opt['left_cyl'] ?> <?= $opt['left_cyl'] != '0.00' ? '| AXIS ' . $opt['left_axis'] : '' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="p-0 text-muted py-1" colspan="2"><b>Khoảng cách PD:</b> <span class="badge bg-warning text-dark"><?= empty($opt['pd']) ? 'Chưa điền' : $opt['pd'] . ' mm' ?></span></td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary opacity-75">Khung Gọng Mặc Định</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="fw-medium text-dark text-nowrap">
                                                <?= number_format($db_item['gia'], 0, ',', '.') ?>đ
                                                <?php if($lens_price > 0): ?>
                                                    <br><small class="text-danger fw-bold">+<?= number_format($lens_price, 0, ',', '.') ?>đ (Tròng)</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <input type="number" name="qty[<?= $cart_key ?>]" value="<?= $qty ?>" min="0" class="form-control text-center shadow-none border-secondary-subtle form-control-sm">
                                            </td>
                                            <td class="text-danger fw-bold text-end pe-4 fs-5 text-nowrap"><?= number_format($subtotal, 0, ',', '.') ?>đ</td>
                                            <td class="text-end pe-3">
                                                <a href="cart.php?action=remove&key=<?= $cart_key ?>" class="btn btn-light text-danger btn-sm rounded-circle shadow-sm" data-bs-toggle="tooltip" title="Xóa món này">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <hr class="mt-0">
                            <div class="d-flex justify-content-between align-items-center mt-3 px-2">
                                <a href="cart.php?action=clear" class="btn btn-outline-danger btn-sm px-3 rounded-pill" onclick="return confirm('Bạn có chắc chắn muốn làm trống giỏ hàng?')"><i class="bi bi-x-circle me-1"></i> Xóa tất cả</a>
                                <div>
                                    <button type="submit" name="update_cart" class="btn btn-secondary px-4 rounded-pill shadow-sm"><i class="bi bi-arrow-repeat me-1"></i> Cập nhật giỏ</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 100px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Tóm tắt đơn hàng</h5>
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Tạm tính giỏ hàng:</span>
                            <span class="fw-medium text-dark"><?= number_format($total_price, 0, ',', '.') ?>đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Phí giao cắt (Lap):</span>
                            <span class="text-success fw-semibold bg-success bg-opacity-10 px-2 rounded">Miễn phí</span>
                        </div>
                        
                        <hr class="my-4 border-secondary-subtle">
                        
                        <div class="d-flex justify-content-between mb-4 mt-3">
                            <span class="fw-bold fs-5 text-dark">Thành tiền:</span>
                            <span class="text-danger fw-bold fs-3"><?= number_format($total_price, 0, ',', '.') ?>đ</span>
                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="checkout.php" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow hover-transform d-flex justify-content-center align-items-center gap-2">
                                Đặt Hàng Thanh Toán <i class="bi bi-caret-right-fill"></i>
                            </a>
                        <?php else: ?>
                            <a href="login.php?redirect=cart" class="btn btn-secondary w-100 rounded-pill py-3 fw-bold shadow d-flex justify-content-center align-items-center gap-2">
                                Đăng Nhập Để Mua Hàng <i class="bi bi-box-arrow-in-right"></i>
                            </a>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <a href="shop.php" class="text-decoration-none text-primary fw-medium"><i class="bi bi-arrow-left-circle me-1"></i> Bổ sung thêm kính khác</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.hover-transform { transition: all 0.2s ease; }
.hover-transform:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(13,202,240,0.3) !important; }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
