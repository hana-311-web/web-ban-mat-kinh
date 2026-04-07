<?php 
include 'includes/header.php';

$id = $_GET['id'] ?? 0;
$sql = "SELECT * FROM san_pham WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<h2>Sản phẩm không tồn tại</h2>";
    exit;
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="assets/images/<?php echo $product['hinh_anh'] ?: 'no-image.jpg'; ?>" class="img-fluid rounded" alt="">
        </div>
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($product['ten_sp']); ?></h2>
            <h4 class="text-primary"><?php echo number_format($product['gia']); ?> VNĐ</h4>
            <p><?php echo nl2br($product['mo_ta']); ?></p>
            <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-success btn-lg">Thêm vào giỏ hàng</button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>