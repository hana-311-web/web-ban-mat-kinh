<?php
include 'includes/header.php';

// Xử lý Xóa Sản Phẩm
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("UPDATE san_pham SET trang_thai = 0 WHERE id = $id");
    header("Location: products.php?msg=deleted");
    exit;
}

// Xử lý Thêm / Sửa Sản Phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten_sp = $_POST['ten_sp'];
    $loai_kinh_id = $_POST['loai_kinh_id'];
    $gia = $_POST['gia'];
    $so_luong = $_POST['so_luong'];
    $trang_thai = $_POST['trang_thai'];
    
    // Xử lý Ảnh
    $hinh_anh = $_POST['old_image'] ?? '';
    if (!empty($_FILES['hinh_anh']['name'])) {
        $target_dir = "../image/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = time() . '_' . basename($_FILES["hinh_anh"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_file)) {
            $hinh_anh = $file_name;
        }
    }

    if (isset($_POST['add_product'])) {
        $stmt = $conn->prepare("INSERT INTO san_pham (ten_sp, loai_kinh_id, gia, so_luong, hinh_anh, trang_thai) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiisi", $ten_sp, $loai_kinh_id, $gia, $so_luong, $hinh_anh, $trang_thai);
        $stmt->execute();
        header("Location: products.php?msg=added");
        exit;
    } elseif (isset($_POST['edit_product'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE san_pham SET ten_sp=?, loai_kinh_id=?, gia=?, so_luong=?, hinh_anh=?, trang_thai=? WHERE id=?");
        $stmt->bind_param("siiisii", $ten_sp, $loai_kinh_id, $gia, $so_luong, $hinh_anh, $trang_thai, $id);
        $stmt->execute();
        header("Location: products.php?msg=updated");
        exit;
    }
}

// Lấy danh sách danh mục
$categories = $conn->query("SELECT * FROM loai_kinh");

// Lấy danh sách sản phẩm
$query = "SELECT sp.*, lk.ten_loai FROM san_pham sp LEFT JOIN loai_kinh lk ON sp.loai_kinh_id = lk.id ORDER BY sp.id DESC";
$products = $conn->query($query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title mb-0">Quản lý Sản phẩm</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="bi bi-plus-lg me-1"></i> Thêm Sản Phẩm
    </button>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        Thao tác thành công!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mt-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên Sản phẩm</th>
                        <th>Loại Kính</th>
                        <th>Giá</th>
                        <th>Kho</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td>
                            <?php if(!empty($row['hinh_anh'])): ?>
                                <img src="../image/<?php echo $row['hinh_anh']; ?>" width="60" class="rounded border">
                            <?php else: ?>
                                <span class="badge bg-secondary">No img</span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold"><?php echo htmlspecialchars($row['ten_sp']); ?></td>
                        <td><?php echo htmlspecialchars($row['ten_loai'] ?? 'Không có'); ?></td>
                        <td class="text-danger fw-bold"><?php echo number_format($row['gia']); ?> đ</td>
                        <td><?php echo $row['so_luong']; ?></td>
                        <td>
                            <?php if($row['trang_thai'] == 1): ?>
                                <span class="badge bg-success">Đang bán</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Ngừng kinh doanh</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal<?php echo $row['id']; ?>" title="Sửa">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <a href="products.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn ẩn sản phẩm này không?')" title="Xóa">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>

                    <!-- Edit Modal per product -->
                    <div class="modal fade" id="editProductModal<?php echo $row['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" enctype="multipart/form-data" class="modal-content">
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title">Sửa Sản Phẩm #<?php echo $row['id']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="old_image" value="<?php echo $row['hinh_anh']; ?>">
                                    
                                    <div class="mb-3">
                                        <label>Tên sản phẩm *</label>
                                        <input type="text" name="ten_sp" class="form-control" value="<?php echo htmlspecialchars($row['ten_sp']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Loại Danh Mục</label>
                                        <select name="loai_kinh_id" class="form-select">
                                            <option value="">-- Chọn loại --</option>
                                            <?php 
                                            // Reset pointer of categories
                                            $categories->data_seek(0);
                                            while($cat = $categories->fetch_assoc()): 
                                            ?>
                                                <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $row['loai_kinh_id'] ? 'selected' : ''; ?>>
                                                    <?php echo $cat['ten_loai']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label>Giá (VNĐ) *</label>
                                            <input type="number" name="gia" class="form-control" value="<?php echo $row['gia']; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Số lượng kho</label>
                                            <input type="number" name="so_luong" class="form-control" value="<?php echo $row['so_luong']; ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label>Hình ảnh mới (Để trống nếu giữ nguyên)</label>
                                        <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                                    </div>
                                    <div class="mb-3">
                                        <label>Trạng thái</label>
                                        <select name="trang_thai" class="form-select">
                                            <option value="1" <?php echo $row['trang_thai'] == 1 ? 'selected' : ''; ?>>Đang bán</option>
                                            <option value="0" <?php echo $row['trang_thai'] == 0 ? 'selected' : ''; ?>>Ngừng kinh doanh</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="edit_product" class="btn btn-warning w-100">Cập nhật Sản phẩm</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Thêm Sản Phẩm Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Tên sản phẩm *</label>
                    <input type="text" name="ten_sp" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Loại Danh Mục</label>
                    <select name="loai_kinh_id" class="form-select">
                        <option value="">-- Chọn loại --</option>
                        <?php 
                        $categories->data_seek(0);
                        while($cat = $categories->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['ten_loai']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Giá (VNĐ) *</label>
                        <input type="number" name="gia" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Số lượng kho</label>
                        <input type="number" name="so_luong" class="form-control" value="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label>Hình ảnh</label>
                    <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label>Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="1">Đang bán</option>
                        <option value="0">Ngừng kinh doanh</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_product" class="btn btn-primary w-100">Lưu Sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
