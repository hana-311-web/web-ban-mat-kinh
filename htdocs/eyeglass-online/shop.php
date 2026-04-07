<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Cửa hàng</h2>
    <div class="row">
        <?php
        $sql = "SELECT * FROM san_pham LIMIT 12";
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            // Kiểm tra nếu tên ảnh trống thì dùng ảnh mặc định
            $imageName = !empty($row['hinh_anh']) ? $row['hinh_anh'] : 'no-image.jpg';
            // Đường dẫn thực tế dựa trên thư mục "image" trong VS Code của bạn
            $imagePath = 'image/' . $imageName; 
            ?>
            
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo $imagePath; ?>" 
                         class="card-img-top" 
                         style="height:200px; object-fit:cover;" 
                         alt="<?php echo htmlspecialchars($row['ten_sp']); ?>">
                    
                    <div class="card-body text-center">
                        <h5 class="card-title text-truncate"><?php echo htmlspecialchars($row['ten_sp']); ?></h5>
                        <p class="card-text text-danger fw-bold"><?php echo number_format($row['gia'], 0, ',', '.'); ?> VNĐ</p>
                        <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary w-100">Xem chi tiết</a>
                    </div>
                </div>
            </div>

        <?php } ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>