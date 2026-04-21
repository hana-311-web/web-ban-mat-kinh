<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <?php
    // Nhận params từ URL
    $cat_slug = $_GET['cat'] ?? '';
    $filter = $_GET['filter'] ?? '';
    $search = $_GET['q'] ?? '';
    $sort = $_GET['sort'] ?? 'newest';
    $price_range = $_GET['price'] ?? '';

    $cat_map = [
        'kinh-can' => 'Kính cận',
        'kinh-ram' => 'Kính râm',
        'gong-kinh' => 'Gọng kính',
        'trong-kinh' => 'Tròng kính'
    ];

    $title = "Tất cả sản phẩm";
    $where_clauses = ["sp.trang_thai = 1"];
    $params = [];
    $types = "";

    // Phân loại danh mục
    if (array_key_exists($cat_slug, $cat_map)) {
        $title = $cat_map[$cat_slug];
        $where_clauses[] = "lk.ten_loai LIKE ?";
        $params[] = "%" . $cat_map[$cat_slug] . "%";
        $types .= "s";
    }

    // Lọc sale
    if ($filter == 'sale') {
        $title = "Sản phẩm Khuyến mãi";
        $where_clauses[] = "sp.gia_cu IS NOT NULL AND sp.gia_cu > sp.gia";
    }

    // Tìm kiếm
    if (!empty($search)) {
        $title = "Tìm kiếm: '" . htmlspecialchars($search) . "'";
        $where_clauses[] = "sp.ten_sp LIKE ?";
        $params[] = "%" . $search . "%";
        $types .= "s";
    }

    // Lọc mức giá
    if ($price_range == 'under_500') {
        $where_clauses[] = "sp.gia < 500000";
    } elseif ($price_range == '500_to_1000') {
        $where_clauses[] = "sp.gia BETWEEN 500000 AND 1000000";
    } elseif ($price_range == 'over_1000') {
        $where_clauses[] = "sp.gia > 1000000";
    }

    // Xử lý sắp xếp
    $order_sql = "sp.id DESC";
    if ($sort == 'price_asc') {
        $order_sql = "sp.gia ASC";
    } elseif ($sort == 'price_desc') {
        $order_sql = "sp.gia DESC";
    }

    $where_sql = implode(' AND ', $where_clauses);
    $sql = "SELECT sp.*, lk.ten_loai FROM san_pham sp LEFT JOIN loai_kinh lk ON sp.loai_kinh_id = lk.id WHERE $where_sql ORDER BY $order_sql LIMIT 24";
    ?>

    <div class="row mb-4">
        <div class="col-12">
            <form method="GET" action="shop.php" id="filterForm">
                <!-- Giữ lại các param hiện tại để lọc không bị mất -->
                <?php if ($cat_slug): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($cat_slug) ?>"><?php endif; ?>
                <?php if ($filter): ?><input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>"><?php endif; ?>
                <?php if ($search): ?><input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>

                <!-- Filter Bar (Red background) -->
                <div class="p-3 rounded d-flex align-items-center flex-wrap gap-3 shadow-sm" style="background-color: #c4302b;">
                    <span class="text-white fw-bold me-2">Bộ lọc</span>
                    
                    <select name="style" class="form-select form-select-sm w-auto d-inline-block border-0 shadow-none px-3 py-1 fw-medium">
                        <option value="">Kiểu dáng</option>
                        <option value="vuong">Vuông</option>
                        <option value="tron">Tròn</option>
                        <option value="mat-meo">Mắt mèo</option>
                        <option value="phi-cong">Phi công (Aviator)</option>
                    </select>
                    
                    <select name="material" class="form-select form-select-sm w-auto d-inline-block border-0 shadow-none px-3 py-1 fw-medium">
                        <option value="">Chất liệu</option>
                        <option value="nhua">Nhựa</option>
                        <option value="kim-loai">Kim loại</option>
                        <option value="titan">Titanium</option>
                    </select>

                    <select name="price" class="form-select form-select-sm w-auto d-inline-block border-0 shadow-none px-3 py-1 fw-medium" onchange="document.getElementById('filterForm').submit();">
                        <option value="">Khoảng giá</option>
                        <option value="under_500" <?= $price_range == 'under_500' ? 'selected' : '' ?>>Dưới 500.000đ</option>
                        <option value="500_to_1000" <?= $price_range == '500_to_1000' ? 'selected' : '' ?>>500.000đ - 1.000.000đ</option>
                        <option value="over_1000" <?= $price_range == 'over_1000' ? 'selected' : '' ?>>Trên 1.000.000đ</option>
                    </select>
                </div>

                <?php
                // Thực thi truy vấn để lấy kết quả
                $stmt = $conn->prepare($sql);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                ?>

                <!-- Result count and sorting aligned to right -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <span style="font-size: 15px;"><span class="fw-bold" style="font-size: 18px;"><?= $result->num_rows ?></span> sản phẩm</span>
                    
                    <div class="d-flex gap-2">
                        <?php $limit = $_GET['limit'] ?? '12'; ?>
                        <select name="limit" class="form-select form-select-sm w-auto shadow-none" onchange="document.getElementById('filterForm').submit();">
                            <option value="12" <?= $limit == '12' ? 'selected' : '' ?>>Hiển thị: 12</option>
                            <option value="24" <?= $limit == '24' ? 'selected' : '' ?>>Hiển thị: 24</option>
                            <option value="36" <?= $limit == '36' ? 'selected' : '' ?>>Hiển thị: 36</option>
                        </select>
                        
                        <select name="sort" class="form-select form-select-sm w-auto shadow-none" onchange="document.getElementById('filterForm').submit();">
                            <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Mặc định</option>
                            <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá từ thấp đến cao</option>
                            <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá từ cao đến thấp</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Product Grid -->
    <div class="row">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $imageName = !empty($row['hinh_anh']) ? $row['hinh_anh'] : 'no-image.jpg';
                $imagePath = 'image/' . $imageName; 
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm border-0 d-flex flex-column transition-hover">
                        <div class="position-relative">
                            <img src="<?php echo $imagePath; ?>" 
                                 class="card-img-top rounded-top" 
                                 style="height:220px; object-fit:cover;" 
                                 alt="<?php echo htmlspecialchars($row['ten_sp']); ?>">
                            <?php if ($row['gia_cu']): ?>
                                <span class="badge bg-danger position-absolute top-0 start-0 m-2 px-2 py-1">Sale</span>
                            <?php endif; ?>
                            <span class="badge bg-primary position-absolute top-0 end-0 m-2 px-2 py-1 shadow"><?php echo htmlspecialchars($row['ten_loai'] ?? 'Khác'); ?></span>
                        </div>
                        
                        <div class="card-body text-center d-flex flex-column align-items-center justify-content-between p-3">
                            <h5 class="card-title w-100 mb-1" style="font-size: 1rem; font-weight: 600; white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?php echo htmlspecialchars($row['ten_sp']); ?>
                            </h5>
                            <div class="mt-2 mb-3 w-100">
                                <?php if ($row['gia_cu']): ?>
                                    <span class="text-muted text-decoration-line-through me-2" style="font-size: 0.85rem;"><?php echo number_format($row['gia_cu'], 0, ',', '.'); ?>đ</span>
                                <?php endif; ?>
                                <span class="card-text text-danger fs-5 fw-bold"><?php echo number_format($row['gia'], 0, ',', '.'); ?> đ</span>
                            </div>
                            <div class="mt-auto w-100 d-flex gap-2 px-1">
                                <a href="cart.php?action=add&id=<?php echo $row['id']; ?>" class="btn btn-outline-danger w-50 rounded-pill fw-semibold border-2 transition-hover text-nowrap" style="font-size: 0.9rem;">
                                    <i class="bi bi-cart-plus"></i> Mua ngay
                                </a>
                                <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary w-50 rounded-pill fw-semibold transition-hover text-nowrap" style="font-size: 0.9rem;">
                                    Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
            }
        } else {
            echo '<div class="col-12"><div class="alert alert-warning py-4 text-center rounded-4 shadow-sm border-0"><i class="bi bi-info-circle fs-3 d-block mb-2 text-warning"></i>Không tìm thấy sản phẩm nào phù hợp với bộ lọc hiện tại! Vui lòng thử mức giá khác.</div></div>';
        }
        ?>
    </div>
</div>

<style>
.transition-hover { transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s, background-color 0.2s; }
.card.transition-hover:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
.btn-outline-primary.transition-hover:hover { background-color: #0ea5e9; border-color: #0ea5e9; color: white !important; }
</style>

<?php include 'includes/footer.php'; ?>
