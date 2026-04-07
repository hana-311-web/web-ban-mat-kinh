<?php
// Bật chế độ báo cáo lỗi nghiêm ngặt (Exceptions) cho MySQLi 
// Giúp try-catch có thể bắt được lỗi nếu kết nối lỡ thất bại
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = 'localhost';
$db   = 'eyeglass_db';
$user = 'root';
$pass = ''; // XAMPP mặc định mật khẩu là rỗng

try {
    // Khởi tạo kết nối đến MySQL
    $conn = new mysqli($host, $user, $pass, $db);
    
    // Đặt bộ mã tiếng Việt đầy đủ (hỗ trợ icon emoji)
    $conn->set_charset("utf8mb4");
    
} catch (mysqli_sql_exception $e) {
    // Bắt lỗi và thông báo bằng mã HTML thân thiện
    die("<h3>Lỗi Hệ Thống</h3><p>Không thể kết nối đến CSDL. Chi tiết lỗi: " . $e->getMessage() . "</p>");
}
?>