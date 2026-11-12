-- ============================================================
-- TMU Document Sharing Website - Seed Data
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- ROLES
-- ============================================================
INSERT INTO roles (id, name, display_name, description) VALUES
(1, 'admin',     'Quản trị viên',    'Quản lý toàn bộ hệ thống'),
(2, 'moderator', 'Kiểm duyệt viên',  'Kiểm duyệt tài liệu và xử lý báo cáo'),
(3, 'member',    'Thành viên',        'Người dùng thông thường');

-- ============================================================
-- ADMIN USER (password: Admin@123456)
-- ============================================================
INSERT INTO users (id, username, email, password_hash, full_name, status, role, email_verified_at) VALUES
(1, 'admin', 'admin@tmu.edu.vn',
 '$2y$12$OLTwgM4ZDS47wD6btSdCdu/sV1AlxzDh8MdF3Ixh7mc/uAtQtb6gC',
 'Quản trị viên TMU', 'active', 'admin', NOW());
-- Note: The hash above is bcrypt of 'Admin@123456' - admin must change on first login

-- ============================================================
-- USER_ROLES (map admin user to admin role)
-- ============================================================
INSERT INTO user_roles (user_id, role_id) VALUES
(1, 1);

-- ============================================================
-- UNIVERSITY
-- ============================================================
INSERT INTO universities (id, name, short_name, slug, description) VALUES
(1, 'Trường Đại học Thương mại', 'TMU', 'dai-hoc-thuong-mai',
 'Trường Đại học Thương mại (TMU) - Đại học hàng đầu về kinh tế, thương mại tại Việt Nam');

-- ============================================================
-- FACULTIES (7 khoa/viện)
-- ============================================================
INSERT INTO faculties (id, university_id, name, short_name, slug, sort_order) VALUES
(1, 1, 'Khoa Lý luận chính trị',         'LLCT', 'khoa-ly-luan-chinh-tri',          1),
(2, 1, 'Viện Quản trị Kinh doanh',        'VQT',  'vien-quan-tri-kinh-doanh',         2),
(3, 1, 'Khoa Kinh tế',                    'KT',   'khoa-kinh-te',                     3),
(4, 1, 'Khoa Thương mại điện tử',         'TMDT', 'khoa-thuong-mai-dien-tu',          4),
(5, 1, 'Khoa Luật',                       'L',    'khoa-luat',                         5),
(6, 1, 'Khoa Toán Kinh tế',               'TKT',  'khoa-toan-kinh-te',                6),
(7, 1, 'Khoa Công nghệ số ứng dụng',      'CNSU', 'khoa-cong-nghe-so-ung-dung',       7);

-- ============================================================
-- SUBJECTS (13 môn học)
-- ============================================================
INSERT INTO subjects (id, faculty_id, name, code, slug, sort_order) VALUES
-- Khoa Lý luận chính trị
(1,  1, 'Triết học Mác – Lênin',                      'THLLN',   'triet-hoc-mac-le-nin',                        1),
(2,  1, 'Kinh tế chính trị Mác – Lênin',              'KTCTLN',  'kinh-te-chinh-tri-mac-le-nin',                2),
-- Viện Quản trị Kinh doanh
(3,  2, 'Quản trị học',                               'QTH',     'quan-tri-hoc',                                1),
-- Khoa Kinh tế
(4,  3, 'Kinh tế học',                                'KTH',     'kinh-te-hoc',                                 1),
-- Khoa Thương mại điện tử
(5,  4, 'Thương mại điện tử căn bản',                 'TMDTCB',  'thuong-mai-dien-tu-can-ban',                  1),
(6,  4, 'Quản trị thương mại điện tử',                'QTTMDT',  'quan-tri-thuong-mai-dien-tu',                 2),
-- Khoa Luật
(7,  5, 'Pháp luật đại cương',                        'PLDC',    'phap-luat-dai-cuong',                         1),
-- Khoa Toán Kinh tế
(8,  6, 'Toán đại cương',                             'TDC',     'toan-dai-cuong',                              1),
(9,  6, 'Kinh tế lượng',                              'KTL',     'kinh-te-luong',                               2),
-- Khoa Công nghệ số ứng dụng
(10, 7, 'Cơ sở dữ liệu',                              'CSDL',    'co-so-du-lieu',                               1),
(11, 7, 'Hệ thống thông tin quản lý',                 'HTTTQL',  'he-thong-thong-tin-quan-ly',                  2),
(12, 7, 'Phân tích và thiết kế hệ thống thông tin',   'PTTK',    'phan-tich-va-thiet-ke-he-thong-thong-tin',    3),
(13, 7, 'Quản trị dự án công nghệ thông tin',         'QTDACNTT','quan-tri-du-an-cong-nghe-thong-tin',          4);

-- ============================================================
-- DOCUMENT TYPES (4 loại)
-- ============================================================
INSERT INTO document_types (id, name, slug, sort_order) VALUES
(1,  'Giáo trình',             'giao-trinh',          1),
(2,  'Slide bài giảng',        'slide-bai-giang',     2),
(3,  'Đề thi',                 'de-thi',              3),
(4,  'Tài liệu khác',          'tai-lieu-khac',       4);

SET FOREIGN_KEY_CHECKS = 1;
