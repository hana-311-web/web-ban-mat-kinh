<?php
include 'includes/header.php';

// Xử lý Cập nhật Trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['trang_thai'];
    
    $stmt = $conn->prepare("UPDATE don_hang SET trang_thai = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    header("Location: orders.php?msg=status_updated");
    exit;
}

// Lấy danh sách đơn hàng
$query = "SELECT d.*, k.username FROM don_hang d LEFT JOIN khach_hang k ON d.khach_hang_id = k.id ORDER BY d.id DESC";
$orders = $conn->query($query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý Đơn hàng</h2>
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
                        <th>Mã Đơn</th>
                        <th>Người Nhận</th>
                        <th>SĐT</th>
                        <th>Tổng tiền</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $orders->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-bold">#<?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['ten_nguoi_nhan'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['sdt_nguoi_nhan'] ?: 'N/A'); ?></td>
                        <td class="text-danger fw-bold"><?php echo number_format($row['tong_tien']); ?> đ</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['ngay_dat'])); ?></td>
                        <td>
                            <?php 
                                $status_colors = [
                                    'pending' => 'warning text-dark',
                                    'confirmed' => 'primary',
                                    'shipped' => 'info text-dark',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $color = $status_colors[$row['trang_thai']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $color; ?>"><?php echo strtoupper($row['trang_thai']); ?></span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#orderDetailModal<?php echo $row['id']; ?>">
                                <i class="bi bi-eye"></i> Xem
                            </button>
                        </td>
                    </tr>

                    <!-- Order Detail Modal -->
                    <div class="modal fade" id="orderDetailModal<?php echo $row['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Chi tiết đơn hàng #<?php echo $row['id']; ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Khách hàng:</strong> <?php echo htmlspecialchars($row['username'] ?? 'Khách lẻ'); ?><br>
                                            <strong>Tên người nhận:</strong> <?php echo htmlspecialchars($row['ten_nguoi_nhan']); ?><br>
                                            <strong>Lập lúc:</strong> <?php echo date('d/m/Y H:i', strtotime($row['ngay_dat'])); ?>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <strong>SĐT:</strong> <?php echo htmlspecialchars($row['sdt_nguoi_nhan']); ?><br>
                                            <strong>Địa chỉ:</strong> <?php echo htmlspecialchars($row['dia_chi_giao_hang']); ?><br>
                                            <strong>Phương thức TT:</strong> <span class="badge bg-secondary"><?php echo $row['phuong_thuc_thanh_toan']; ?></span>
                                        </div>
                                    </div>
                                    <?php if(!empty($row['ghi_chu'])): ?>
                                        <div class="alert alert-info py-2"><strong>Ghi chú:</strong> <?php echo nl2br(htmlspecialchars($row['ghi_chu'])); ?></div>
                                    <?php endif; ?>
                                    
                                    <h6 class="fw-bold mt-4">Sản Phẩm Đã Đặt</h6>
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tên SP</th>
                                                <th>SL</th>
                                                <th>Đơn giá</th>
                                                <th>Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            // Lấy chi tiết
                                            $order_id = $row['id'];
                                            $details = $conn->query("SELECT c.*, s.ten_sp FROM chi_tiet_don_hang c LEFT JOIN san_pham s ON c.san_pham_id = s.id WHERE c.don_hang_id = $order_id");
                                            while($d = $details->fetch_assoc()):
                                                $thanh_tien = $d['so_luong'] * $d['gia_ban'];
                                                
                                                // Xử lý chuỗi JSON tùy chọn để thợ kỹ thuật nhìn thấy
                                                $tuy_chon_str = '';
                                                if (isset($d['tuy_chon']) && !empty($d['tuy_chon'])) {
                                                    $opt = json_decode($d['tuy_chon'], true);
                                                    if ($opt) {
                                                        $tuy_chon_str = "<div class='small border border-warning rounded p-2 mt-2 bg-light' style='font-size: 0.8rem; line-height: 1.4'>
                                                            <b class='text-danger'>Loại Tròng:</b> " . htmlspecialchars($opt['lens_type']) . "<br>
                                                            <b class='text-primary'>Mắt Phải (R):</b> SPH {$opt['right_sph']} | CYL {$opt['right_cyl']} | AXIS {$opt['right_axis']}<br>
                                                            <b class='text-primary'>Mắt Trái (L):</b> SPH {$opt['left_sph']} | CYL {$opt['left_cyl']} | AXIS {$opt['left_axis']}<br>
                                                            <b class='text-success'>Khoảng cách PD:</b> {$opt['pd']} mm
                                                        </div>";
                                                    }
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($d['ten_sp'] ?? 'Sản phẩm đã bị xóa'); ?></span>
                                                    <?php echo $tuy_chon_str; ?>
                                                </td>
                                                <td class="align-middle text-center fw-bold"><?php echo $d['so_luong']; ?></td>
                                                <td class="align-middle text-end"><?php echo number_format($d['gia_ban']); ?>đ</td>
                                                <td class="text-danger fw-bold align-middle text-end"><?php echo number_format($thanh_tien); ?>đ</td>
                                            </tr>
                                            <?php endwhile; ?>
                                            <tr class="fw-bold bg-light">
                                                <td colspan="3" class="text-end">Tổng cộng:</td>
                                                <td class="text-danger"><?php echo number_format($row['tong_tien']); ?> đ</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Form Cập nhật trạng thái -->
                                    <form method="POST" class="mt-4 border bg-light p-3 rounded d-flex align-items-center">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        <strong class="me-3">Đổi trạng thái:</strong>
                                        <select name="trang_thai" class="form-select w-auto me-2">
                                            <option value="pending" <?php echo $row['trang_thai']=='pending'?'selected':''; ?>>Pending (Chờ duyệt)</option>
                                            <option value="confirmed" <?php echo $row['trang_thai']=='confirmed'?'selected':''; ?>>Confirmed (Đã xác nhận)</option>
                                            <option value="shipped" <?php echo $row['trang_thai']=='shipped'?'selected':''; ?>>Shipped (Đang giao)</option>
                                            <option value="completed" <?php echo $row['trang_thai']=='completed'?'selected':''; ?>>Completed (Hoàn thành)</option>
                                            <option value="cancelled" <?php echo $row['trang_thai']=='cancelled'?'selected':''; ?>>Cancelled (Hủy)</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-primary">Cập nhật</button>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

