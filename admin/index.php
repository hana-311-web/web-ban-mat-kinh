<?php
include 'includes/header.php';

// Lấy số liệu thống kê
$total_products = $conn->query("SELECT COUNT(*) as count FROM san_pham")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM don_hang WHERE trang_thai = 'pending'")->fetch_assoc()['count'];
$total_customers = $conn->query("SELECT COUNT(*) as count FROM khach_hang WHERE vai_tro = 'khach'")->fetch_assoc()['count'];
?>

<h2 class="page-title">👋 Xin chào, <?php echo $_SESSION['ho_ten'] ?? 'Admin'; ?>!</h2>
<p class="lead text-secondary">Chào mừng bạn đến với trang quản trị Eyeglass Online</p>

<div class="row mt-4">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary h-100 mb-3">
            <div class="card-body">
                <h5><i class="bi bi-box-seam me-2"></i> Sản phẩm</h5>
                <h3 class="display-5 fw-bold"><?php echo number_format($total_products); ?></h3>
                <small class="text-white-50">Tổng số sản phẩm hiện có</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success h-100 mb-3">
            <div class="card-body">
                <h5><i class="bi bi-cart-check me-2"></i> Đơn hàng đang chờ</h5>
                <h3 class="display-5 fw-bold"><?php echo number_format($total_orders); ?></h3>
                <small class="text-white-50">Đơn hàng cần xử lý</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-warning h-100 mb-3">
            <div class="card-body">
                <h5><i class="bi bi-people me-2"></i> Khách hàng</h5>
                <h3 class="display-5 fw-bold text-white"><?php echo number_format($total_customers); ?></h3>
                <small class="text-white-50">Khách hàng đã đăng ký</small>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4 p-4">
    <h4 class="page-title">Hoạt động gần đây</h4>
    <p class="mb-0 text-muted">Tính năng này đang được phát triển.</p>
</div>

<?php include 'includes/footer.php'; ?>z
