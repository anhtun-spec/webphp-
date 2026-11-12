# Website Chia Sẻ Tài Liệu Học Tập TMU (Trường Đại học Thương mại)

Hệ thống thư viện học liệu số trực tuyến được xây dựng hoàn toàn bằng **PHP OOP thuần (mô hình MVC - Model-View-Controller)**, không sử dụng framework, kết hợp cùng **MySQL**, **Bootstrap 5** và **JavaScript Vanilla**.

---

## 1. Cấu Trúc Dự Án (Mô hình MVC Thuần)

```
Web Php/
├── app/
│   ├── Controllers/            # 19 Controller xử lý điều hướng & nghiệp vụ
│   │   ├── Admin/              # Phân hệ Quản trị (Dashboard, Duyệt bài, User, Danh mục, Báo cáo, Thống kê...)
│   │   ├── AuthController.php
│   │   ├── CategoryController.php
│   │   ├── CommentController.php
│   │   ├── DocumentController.php
│   │   ├── FavoriteController.php
│   │   ├── HomeController.php
│   │   ├── ProfileController.php
│   │   ├── RatingController.php
│   │   ├── ReportController.php
│   │   ├── SearchController.php
│   │   └── ErrorController.php
│   ├── Core/                   # Bộ khung Core MVC tự xây dựng (PHP OOP)
│   │   ├── App.php             # Khởi tạo và điều phối ứng dụng (Bootstrap)
│   │   ├── Config.php          # Quản lý cấu hình dạng dot-notation
│   │   ├── Controller.php      # Base Controller (render view, flash, validate, auth...)
│   │   ├── Database.php        # Singleton PDO kết nối CSDL an toàn
│   │   ├── Model.php           # Base Model (CRUD, pagination, prepared statements)
│   │   ├── Router.php          # Dynamic URL Router với tham số Regex
│   │   └── Validator.php       # Kiểm tra hợp lệ dữ liệu đầu vào
│   ├── Helpers/
│   │   └── functions.php       # 25+ hàm tiện ích toàn cục (XSS escaping, CSRF, URL, format...)
│   ├── Models/                 # 14 Model nghiệp vụ (OOP đại diện cho các bảng CSDL)
│   │   ├── Comment.php, Document.php, DocumentRequest.php, DocumentType.php,
│   │   ├── DocumentVersion.php, DownloadHistory.php, Faculty.php, Favorite.php,
│   │   ├── Notification.php, Rating.php, Report.php, Subject.php, University.php, User.php
│   │   └── User.php
│   ├── Services/
│   │   ├── AuthService.php     # Đăng ký, đăng nhập, bảo vệ brute force, reset mật khẩu
│   │   └── FileService.php     # Upload file, kiểm tra MIME/checksum, quản lý lưu trữ
│   └── Views/                  # Giao diện người dùng và quản trị (Bootstrap 5)
│       ├── admin/              # Giao diện trang quản trị viên
│       ├── auth/               # Đăng nhập, đăng ký, quên mật khẩu
│       ├── categories/         # Danh mục Khoa/Viện, Môn học
│       ├── documents/          # Danh sách, chi tiết, đăng tải, sửa, xem trước PDF
│       ├── errors/             # Trang lỗi 403, 404
│       ├── home/               # Trang chủ
│       ├── layouts/            # Layout chính (main.php, admin.php)
│       ├── profile/            # Hồ sơ, tài liệu đã đăng, yêu thích, lịch sử tải, thông báo
│       └── search/             # Kết quả tìm kiếm & bộ lọc
├── config/
│   ├── config.php              # Cấu hình hệ thống (DB, Upload, Session, Pagination)
│   └── routes.php              # Bảng định tuyến 57 routes
├── database/
│   ├── schema.sql              # Cấu trúc CSDL chuẩn hóa (15 bảng, InnoDB, utf8mb4)
│   ├── seed.sql                # Dữ liệu mẫu (7 Khoa, 13 Môn học TMU, 10 loại tài liệu, Admin)
│   └── README.md               # Hướng dẫn chi tiết tạo và import CSDL
├── public/                     # Thư mục gốc Web Server (Document Root)
│   ├── .htaccess               # URL Rewrite về index.php
│   ├── index.php               # Điểm đón request duy nhất (Front Controller)
│   └── assets/                 # CSS, JS, hình ảnh
├── storage/                    # Nơi lưu trữ tệp tin tải lên an toàn (ngoài public access)
│   ├── avatars/                # Ảnh đại diện
│   └── documents/              # Tệp tài liệu (pending/ và approved/)
├── composer.json               # Chuẩn Autoloading PSR-4
└── README.md
```

---

## 2. Hướng Dẫn Cài Đặt & Chạy Ứng Dụng

### Bước 1: Khởi động MySQL & Apache (XAMPP hoặc Laragon)
1. Mở **XAMPP Control Panel**.
2. Nhấn **Start** ở cả 2 dịch vụ **Apache** và **MySQL**.

### Bước 2: Tạo Cơ Sở Dữ Liệu & Import Dữ Liệu
Mở trình duyệt truy cập `http://localhost/phpmyadmin` (hoặc mở Command Prompt):

1. Tạo database mới:
```sql
CREATE DATABASE tmu_documents CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
2. Import file cấu trúc bảng:
   - Chọn database `tmu_documents` -> Nhấn tab **Import** -> Chọn file `database/schema.sql` -> Nhấn **Import** (hoặc Go).
3. Import file dữ liệu mẫu:
   - Chọn tab **Import** -> Chọn file `database/seed.sql` -> Nhấn **Import** (hoặc Go).

> Hoặc qua dòng lệnh MySQL:
```bash
mysql -u root -p tmu_documents < "c:\Users\admin\Desktop\Web Php\database\schema.sql"
mysql -u root -p tmu_documents < "c:\Users\admin\Desktop\Web Php\database\seed.sql"
```

### Bước 3: Truy cập Website
Mở trình duyệt web và truy cập theo 1 trong 2 cách sau:

- **Cách 1 (Khuyên dùng - PHP Built-in Server):**
  Mở terminal tại thư mục dự án và chạy:
  ```bash
  cd "c:\Users\admin\Desktop\Web Php"
  C:\xampp\php\php.exe -S localhost:8000 -t public
  ```
  Sau đó mở trình duyệt vào: `http://localhost:8000`

- **Cách 2 (Qua XAMPP htdocs):**
  Nếu bạn đặt thư mục dự án trong `C:\xampp\htdocs\Web Php`, mở trình duyệt vào:
  `http://localhost/Web%20Php/public`

---

## 3. Tài Khoản Quản Trị Mặc Định

Hệ thống đã tạo sẵn tài khoản Admin trong `seed.sql`:
- **Email:** `admin@tmu.edu.vn`
- **Mật khẩu:** `Admin@123456`
- **Vai trò:** Quản trị viên (Admin)

*(Gợi ý: Đăng nhập và đổi mật khẩu trong trang Hồ sơ cá nhân sau lần đăng nhập đầu tiên).*

---

## 4. Các Tính Năng Đã Hoàn Thiện

### 👥 Khách vãng lai
- [x] Xem trang chủ giới thiệu tài liệu mới, nổi bật và danh mục Khoa/Viện.
- [x] Tìm kiếm tài liệu theo từ khóa, lọc theo Khoa, Môn học, Loại tài liệu, Học kỳ, Năm học.
- [x] Xem chi tiết tài liệu, đọc mô tả và metadata.
- [x] Đọc trước trực tiếp (Preview) tài liệu định dạng PDF trên trình duyệt.
- [x] Xem danh sách và thông tin các Khoa/Viện và Môn học TMU.
- [x] Đăng ký tài khoản sinh viên mới, quên mật khẩu.

### 🎓 Thành viên (Sinh viên đã đăng nhập)
- [x] Đăng nhập, đăng xuất, lưu phiên đăng nhập.
- [x] Tải xuống file tài liệu (hệ thống tự động ghi nhận lượt tải và lịch sử).
- [x] Đăng tải tài liệu mới: upload file (PDF, Word, Excel, PowerPoint, Zip lên đến 50MB), chọn Khoa/Môn/Loại tài liệu, nhập metadata. Tệp tự động vào trạng thái **Chờ duyệt**.
- [x] Quản lý "Tài liệu của tôi": theo dõi trạng thái (Chờ duyệt, Đã duyệt, Bị từ chối), sửa và xóa tài liệu.
- [x] Chấm điểm đánh giá (1-5 sao) và nhận xét tài liệu.
- [x] Bình luận và trả lời bình luận nhiều cấp dưới từng tài liệu.
- [x] Đánh dấu tài liệu vào danh sách **Yêu thích** (lưu tài liệu).
- [x] Báo cáo vi phạm (sai môn, trùng lặp, bản quyền, phản cảm...).
- [x] Quản lý thông tin cá nhân, cập nhật ảnh đại diện, đổi mật khẩu.
- [x] Xem thông báo khi tài liệu được duyệt/từ chối.

### 🛡️ Quản trị viên (Admin) & Kiểm duyệt viên (Moderator)
- [x] **Dashboard:** Thống kê tổng quan số người dùng, tài liệu công khai, chờ duyệt, lượt tải, báo cáo.
- [x] **Kiểm duyệt tài liệu:** Xem danh sách chờ duyệt, duyệt tài liệu (file tự động chuyển sang kho public), từ chối tài liệu kèm lý do phản hồi cho sinh viên.
- [x] **Quản lý tài liệu:** Xem toàn bộ tài liệu, lọc theo trạng thái, ẩn hoặc xóa tài liệu vi phạm.
- [x] **Quản lý danh mục động:** Thêm mới Khoa/Viện, thêm môn học, thêm loại tài liệu, ẩn/hiện hoặc đổi thứ tự hiển thị mà không cần sửa mã nguồn.
- [x] **Quản lý người dùng (Admin):** Xem danh sách, tìm kiếm, phân quyền (Thành viên / Kiểm duyệt / Admin), khóa hoặc mở khóa tài khoản.
- [x] **Quản lý báo cáo:** Xem khiếu nại của sinh viên, xử lý và lưu ghi chú.
- [x] **Quản lý bình luận:** Kiểm tra và ẩn các bình luận thô tục, vi phạm nội quy.
- [x] **Báo cáo thống kê:** Top 10 tài liệu được tải nhiều nhất, Top thành viên tích cực nhất, biểu đồ phân bố tài liệu theo Khoa/Viện.

---

## 5. Tiêu Chuẩn Bảo Mật Đã Triển Khai

- **Chống SQL Injection:** 100% câu truy vấn dùng PDO Prepared Statements với tham số ràng buộc.
- **Chống XSS:** Mọi biến xuất ra màn hình đều qua hàm `e()` (`htmlspecialchars` với cờ `ENT_QUOTES | ENT_HTML5`).
- **Chống CSRF:** Mọi form POST đều có token ẩn `_csrf_token` và kiểm tra nghiêm ngặt phía server.
- **Băm mật khẩu:** Sử dụng thuật toán `PASSWORD_BCRYPT` với cost 12 an toàn.
- **Bảo mật Upload:** Kiểm tra đuôi file hợp lệ, kiểm tra MIME type qua `finfo`, kiểm tra dung lượng tối đa 50MB, đổi tên file theo mã băm SHA-256 để chống tấn công thực thi file mã độc.
- **Phân quyền đa tầng (RBAC):** Middleware kiểm tra quyền ở phía máy chủ trên mọi controller và action nhạy cảm.

