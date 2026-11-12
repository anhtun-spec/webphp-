<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <title><?= e($title ?? 'Admin — TMU Tài Liệu') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 250px; min-width: 250px; background: #1a1a2e;
            color: white; display: flex; flex-direction: column;
        }
        .admin-sidebar .brand {
            padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,.1);
            font-size: 1.1rem; font-weight: 700; color: white; text-decoration: none;
        }
        .admin-sidebar .nav-link, .admin-offcanvas .nav-link {
            color: rgba(255,255,255,.75); padding: .6rem 1.25rem;
            border-radius: .4rem; margin: 2px .75rem; display: flex; align-items: center; text-decoration: none;
        }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active,
        .admin-offcanvas .nav-link:hover, .admin-offcanvas .nav-link.active {
            color: white; background: rgba(255,255,255,.12);
        }
        .admin-sidebar .nav-link i, .admin-offcanvas .nav-link i { width: 20px; margin-right: 8px; }
        .admin-sidebar .nav-section, .admin-offcanvas .nav-section {
            font-size: .7rem; text-transform: uppercase; letter-spacing: .08em;
            color: rgba(255,255,255,.4); padding: .75rem 1.5rem .25rem;
        }
        .admin-content { flex: 1; overflow-x: auto; display: flex; flex-direction: column; min-height: 100vh; }
        .admin-topbar {
            background: white; padding: .75rem 1.5rem;
            border-bottom: 1px solid #dee2e6;
            display: flex; align-items: center; justify-content: space-between;
        }
        .admin-main { padding: 1.5rem; }
        .admin-footer {
            margin-top: auto; background: white; padding: .9rem 1.5rem;
            border-top: 1px solid #dee2e6; font-size: .85rem; color: #6c757d;
        }
        @media (max-width: 768px) {
            .admin-sidebar { display: none; }
        }
    </style>
</head>
<body>

<?php
$curPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$pendingCount = (int)(new \App\Models\Document())->count(['status' => 'pending', 'deleted_at' => null]);
ob_start();
?>
<nav class="mt-2 flex-grow-1">
    <div class="nav-section">Tổng quan</div>
    <a href="<?= url('/admin') ?>" class="nav-link <?= (str_ends_with($curPath, '/admin') || str_ends_with($curPath, '/admin/')) ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i>Bảng điều khiển
    </a>

    <div class="nav-section mt-2">Nội dung</div>
    <a href="<?= url('/admin/moderation') ?>" class="nav-link <?= str_contains($curPath, '/admin/moderation') ? 'active' : '' ?>">
        <i class="bi bi-clipboard-check"></i>Kiểm duyệt
        <?php if ($pendingCount > 0): ?>
            <span class="badge bg-danger ms-auto"><?= $pendingCount ?></span>
        <?php endif; ?>
    </a>
    <a href="<?= url('/admin/documents') ?>" class="nav-link <?= (str_contains($curPath, '/admin/documents') && !str_contains($curPath, '/admin/moderation')) ? 'active' : '' ?>">
        <i class="bi bi-files"></i>Tài liệu
    </a>

    <div class="nav-section mt-2">Danh mục</div>
    <a href="<?= url('/admin/categories') ?>" class="nav-link <?= str_contains($curPath, '/admin/categories') ? 'active' : '' ?>">
        <i class="bi bi-diagram-3"></i>Khoa / Môn / Loại
    </a>

    <div class="nav-section mt-2">Người dùng</div>
    <a href="<?= url('/admin/users') ?>" class="nav-link <?= str_contains($curPath, '/admin/users') ? 'active' : '' ?>">
        <i class="bi bi-people"></i>Quản lý người dùng
    </a>

    <div class="nav-section mt-2">Thống kê</div>
    <a href="<?= url('/admin/statistics') ?>" class="nav-link <?= str_contains($curPath, '/admin/statistics') ? 'active' : '' ?>">
        <i class="bi bi-bar-chart"></i>Thống kê
    </a>
</nav>
<div style="padding:1rem 1.5rem; border-top:1px solid rgba(255,255,255,.1)">
    <a href="<?= url('/') ?>" class="nav-link">
        <i class="bi bi-arrow-left"></i>Về trang người dùng
    </a>
</div>
<?php $adminNavHtml = ob_get_clean(); ?>

<div class="admin-wrapper">
    <!-- Desktop Sidebar -->
    <div class="admin-sidebar">
        <a href="<?= url('/admin') ?>" class="brand">
            <i class="bi bi-shield-check me-2"></i>TMU Admin
        </a>
        <?= $adminNavHtml ?>
    </div>

    <!-- Mobile Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start text-white admin-offcanvas" style="background: #1a1a2e; width: 260px;" tabindex="-1" id="adminMobileSidebar">
        <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
            <a href="<?= url('/admin') ?>" class="text-white text-decoration-none fw-bold fs-5">
                <i class="bi bi-shield-check me-2"></i>TMU Admin
            </a>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
            <?= $adminNavHtml ?>
        </div>
    </div>

    <!-- Main content -->
    <div class="admin-content">
        <!-- Topbar -->
        <div class="admin-topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileSidebar" aria-label="Mở menu">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h6 class="mb-0 fw-semibold"><?= e($title ?? 'Admin') ?></h6>
            </div>
            <div class="d-flex align-items-center gap-3">
                <?php $user = currentUser(); ?>
                <span class="text-muted small"><?= e($user['full_name'] ?? '') ?> (<?= e($user['role'] ?? '') ?>)</span>
                <form action="<?= url('/logout') ?>" method="POST" class="mb-0">
                    <?= csrfField() ?>
                    <button class="btn btn-sm btn-outline-danger" type="submit" title="Đăng xuất">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Flash -->
        <div class="admin-main pb-0">
            <?php foreach (['success', 'error', 'warning', 'info'] as $t): ?>
                <?php foreach (getFlash($t) as $msg): ?>
                    <?php $bt = $t === 'error' ? 'danger' : $t; ?>
                    <div class="alert alert-<?= $bt ?> alert-dismissible fade show">
                        <?= e($msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <?php foreach (getFlash('errors') as $serialized): ?>
                <?php $errArr = @unserialize($serialized); if (is_array($errArr)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Vui lòng kiểm tra lại thông tin:</strong>
                        <ul class="mb-0 mt-1">
                            <?php foreach ($errArr as $msgs): ?>
                                <?php foreach ((array)$msgs as $m): ?>
                                    <li><?= e($m) ?></li>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Page content -->
        <div class="admin-main flex-grow-1">
            <?= $content ?>
        </div>

        <!-- Admin Footer -->
        <footer class="admin-footer d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <span>&copy; <?= date('Y') ?> <strong>TMU Tài Liệu</strong> — Hệ thống quản trị học liệu sinh viên TMU.</span>
            <a href="<?= url('/') ?>" class="text-decoration-none text-primary fw-semibold">
                <i class="bi bi-box-arrow-up-right me-1"></i>Xem trang công khai
            </a>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/main.js') ?>"></script>
<?php clearOldInput(); ?>
</body>
</html>

