<?php
session_start();
include '../includes/db.php';

// Nếu đã đăng nhập với quyền admin, chuyển hướng về trang quản trị
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Dùng prepared statement để tránh SQL Injection
    $sql = "SELECT * FROM khach_hang WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Kiểm tra mật khẩu
        if (password_verify($password, $user['password'])) {
            // Kiểm tra xem người dùng có phải là admin không
            if ($user['vai_tro'] === 'admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role']    = $user['vai_tro'];
                $_SESSION['ho_ten']  = $user['ho_ten'];
                
                header("Location: index.php");
                exit;
            } else {
                $error = "Bạn không có quyền truy cập trang quản trị!";
            }
        } else {
            $error = "Sai mật khẩu!";
        }
    } else {
        $error = "Tài khoản không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập Admin - Eyeglass Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #343a40; /* Nền tối đặc trưng của trang admin */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            background-color: #0dcaf0; /* Màu xanh nổi bật */
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .login-header i {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0dcaf0;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header text-dark">
        <i class="fas fa-user-shield"></i>
        <h4>Quản Trị Viên</h4>
    </div>
    <div class="card-body p-4">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-muted">Tài khoản</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Nhập username" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
                </div>
            </div>
            <button type="submit" class="btn btn-info w-100 text-white fw-bold">Đăng Nhập</button>
        </form>
        <div class="text-center mt-3">
            <a href="../index.php" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a>
        </div>
    </div>
</div>

</body>
</html>
