**Website bán kính mắt trực tuyến**  
## 1. Giới thiệu dự án
Eyeglass Online là hệ thống website thương mại điện tử giúp khách hàng mua kính mắt **có sẵn**, đặt trước hoặc **làm theo đơn** (kính theo nhu cầu) ngay tại nhà.
Website giải quyết các vấn đề thực tế:
- Không cần sắp xếp thời gian đi cửa hàng, chờ tư vấn/đo mắt
- Không bị giới hạn bởi tồn kho của từng cửa hàng
- Dễ dàng so sánh mẫu, giá, tính năng giữa nhiều loại kính
- Phù hợp với người bận rộn hoặc ở xa

## 2. Tính năng chính
### 2.1. Phía Khách hàng (Customer)
1. Đăng ký / Đăng nhập (email + mật khẩu)
2. Trang chủ + Danh mục sản phẩm (kính cận, kính râm, gọng kính,tròng kính,khuyến mãi)
3. Tìm kiếm + Lọc (giá, loại kính, thương hiệu, độ cận)
4. Xem chi tiết sản phẩm + Ảnh 
5. Thêm vào giỏ hàng (AJAX – không reload trang)
6. Giỏ hàng (cập nhật số lượng, xóa, tính tổng tiền)
7. Đặt hàng (2 loại: kính sẵn hoặc kính theo đơn)
8. Tư vấn nhanh (gửi yêu cầu đo mắt / chọn gọng + tròng)
9. Xem lịch sử đơn hàng + trạng thái
10. Trang cá nhân (cập nhật thông tin)

### 2.2. Phía Admin (Quản trị viên)
1. Dashboard
2. Quản lý sản phẩm (thêm/sửa/xóa + upload ảnh)
3. Quản lý đơn hàng (xác nhận, cập nhật trạng thái)
4. Quản lý tài khoản khách hàng
5. Thống kê đơn hàng (số lượng, doanh thu – dùng PHP + MySQL)

## 3. Công nghệ sử dụng

| Phần          | Công nghệ                          | Mục đích                                      |
|---------------|------------------------------------|-----------------------------------------------|
| Frontend      | HTML + CSS + Bootstrap 5           | Giao diện responsive, đẹp, mobile-friendly    |
| Interactivity | JavaScript (vanilla + Bootstrap JS)| Giỏ hàng động, filter, validation form        |
| Backend       | PHP 8                              | Xử lý đăng nhập, CRUD, giỏ hàng, đặt hàng     |
| Database      | MySQL                              | Lưu sản phẩm, đơn hàng, tài khoản             |
| Khác          | AJAX (JS + PHP)                    | Thêm/xóa giỏ hàng không reload trang          |

## 4. Cấu trúc thư mục dự án

eyeglass-online/
├── admin/                  # Dashboard và quản lý dành cho Quản trị viên
├── assets/                 # Tài nguyên tĩnh (CSS, JS, Hình ảnh)
│   ├── css/                # Định dạng giao diện chính
│   ├── js/                 # Xử lý tương tác phía người dùng
│   └── image/              # Hình ảnh sản phẩm và biểu tượng
├── includes/               # Các thành phần hệ thống dùng chung
│   ├── db.php              # Kết nối Database
│   ├── functions.php       # Logic xử lý nghiệp vụ
│   ├── header.php          # Navbar & Metadata
│   └── footer.php          # Thông tin chân trang
├── sql/                    # Scripts cơ sở dữ liệu
│   └── database.sql        # File khởi tạo cấu trúc dữ liệu
├── index.php               # Trang chủ dự án
├── shop.php                # Danh mục sản phẩm
├── product-detail.php      # Chi tiết sản phẩm
├── cart.php                # Quản lý giỏ hàng
├── login.php / register.php # Hệ thống xác thực người dùng
└── logout.php              # Xử lý đăng xuất
