<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h2>Cửa hàng</h2>
    <div class="row">
        <?php
        $sql = "SELECT * FROM san_pham LIMIT 12";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo '
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <img src="assets/images/' . ($row['hinh_anh'] ?: 'no-image.jpg') . '" class="card-img-top" style="height:200px; object-fit:cover;">
                    <div class="card-body">
                        <h5 class="card-title">' . htmlspecialchars($row['ten_sp']) . '</h5>
                        <p class="card-text">' . number_format($row['gia']) . ' VNĐ</p>
                        <a href="product-detail.php?id=' . $row['id'] . '" class="btn btn-primary">Xem chi tiết</a>
                    </div>
                </div>
            </div>';
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>