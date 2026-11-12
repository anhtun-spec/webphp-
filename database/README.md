# Database Setup

## Requirements
- MySQL 8.0+ or MariaDB 10.6+
- Database name: `tmu` (hoặc cấu hình trong `config/config.php`)

## Setup Instructions

1. Create the database:
```sql
CREATE DATABASE tmu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import schema:
```bash
mysql -u root -p tmu < schema.sql
```

3. Import seed data:
```bash
mysql -u root -p tmu < seed.sql
```

## Default Admin Account
- **Email:** admin@tmu.edu.vn
- **Password:** Admin@123456
- **⚠️ Change this password immediately after first login!**

## Note on Faculty/Subject Names
The 7 faculties and 13 subjects seeded here are the **initial working data** based on the project specification. They have NOT been officially verified against TMU's current organizational structure. Admin can add, edit, or hide any category through the Admin panel without touching the code.

## Cấu hình Tìm kiếm FULLTEXT cho Tiếng Việt

Mặc định MySQL/InnoDB chỉ đánh chỉ mục (index) các từ khóa có độ dài từ 3 hoặc 4 ký tự trở lên (`innodb_ft_min_token_size = 3` trên MySQL 8.0 hoặc `4` trên các phiên bản cũ hơn). Trong tiếng Việt, nhiều từ khóa học tập quan trọng có độ dài ngắn chỉ 2 ký tự (ví dụ: "Đề", "Bài", "Thi", "Sử", "Lý").

Để tối ưu hóa tìm kiếm FULLTEXT cho tiếng Việt, quản trị viên máy chủ nên cấu hình như sau:

1. Mở và chỉnh sửa tệp cấu hình máy chủ MySQL/MariaDB (`my.cnf` trên Linux hoặc `my.ini` trên Windows / XAMPP):
```ini
[mysqld]
innodb_ft_min_token_size = 2
ft_min_word_len = 2
```

2. Khởi động lại dịch vụ MySQL / MariaDB (ví dụ: qua `systemctl restart mysql` hoặc nút **Restart** trên XAMPP Control Panel).

3. Đánh lại chỉ mục FULLTEXT cho bảng `documents` bằng một trong các câu lệnh SQL sau:
```sql
ALTER TABLE documents DROP INDEX ft_search, ADD FULLTEXT INDEX ft_search(title, description);
```
*(Nếu sử dụng bảng MyISAM, có thể chạy lệnh: `REPAIR TABLE documents;`)*.

