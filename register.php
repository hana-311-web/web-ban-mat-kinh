<?php 
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $ho_ten   = trim($_POST['ho_ten']);

    $sql = "INSERT INTO khach_hang (username, email, password, ho_ten, vai_tro) 
            VALUES (?, ?, ?, ?, 'khach')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $password, $ho_ten);
    
    if ($stmt->execute()) {
        echo "<div class='alert alert-success text-center mt-3'>Đăng ký thành công! <a href='login.php'>Đăng nhập ngay</a></div>";
    } else {
        echo "<div class='alert alert-danger text-center mt-3'>Lỗi: Username hoặc Email đã tồn tại!</div>";
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center bg-primary text-white">
                    <h4>Đăng ký tài khoản</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Họ và tên</label>
                            <input type="text" name="ho_ten" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="login.php">Đã có tài khoản? Đăng nhập ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
