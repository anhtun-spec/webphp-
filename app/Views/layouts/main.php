<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <title><?= e($title ?? 'TMU Tài Liệu') ?></title>
    <link rel="icon" type="image/png" href="<?= asset('images/favicon.png') ?>">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body id="top">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= url('/') ?>">
            <i class="bi bi-journal-bookmark-fill me-2"></i>TMU Tài Liệu
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <!-- Search bar -->
            <form class="d-flex mx-auto w-50" action="<?= url('/search') ?>" method="GET">
                <input class="form-control me-2" type="search" name="q"
                       placeholder="Tìm kiếm tài liệu..."
                       value="<?= e($_GET['q'] ?? '') ?>">
                <button class="btn btn-light" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('/documents') ?>">
                        <i class="bi bi-files me-1"></i>Tài liệu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('/faculties') ?>">
                        <i class="bi bi-diagram-3 me-1"></i>Danh mục
                    </a>
                </li>

                <?php if (auth()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/documents/create') ?>">
                            <i class="bi bi-cloud-upload me-1"></i>Đăng tải
                        </a>
                    </li>

                    <!-- Notifications -->
                    <?php
                        $notifModel = new \App\Models\Notification();
                        $unreadCount = $notifModel->getUnreadCount(userId());
                    ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= url('/notifications') ?>">
                            <i class="bi bi-bell fs-5"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= $unreadCount > 99 ? '99+' : $unreadCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- User dropdown -->
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#"
                           data-bs-toggle="dropdown">
                            <?php $user = currentUser(); ?>
                            <?php $avatarSrc = avatarUrl((int)$user['id'], $user['avatar'] ?? null); ?>
                            <?php if ($avatarSrc): ?>
                                <img src="<?= e($avatarSrc) ?>"
                                     class="rounded-circle" width="32" height="32" style="object-fit:cover"
                                     alt="<?= e($user['full_name']) ?>">
                            <?php else: ?>
                                <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center fw-bold"
                                     style="width:32px;height:32px;font-size:0.9rem">
                                    <?= mb_substr($user['full_name'], 0, 1) ?>
                                </div>
                            <?php endif; ?>
                            <span class="d-none d-lg-inline"><?= e($user['full_name']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header"><?= e($user['email']) ?></h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= url('/profile') ?>">
                                <i class="bi bi-person me-2"></i>Hồ sơ cá nhân
                            </a></li>
                            <li><a class="dropdown-item" href="<?= url('/my-documents') ?>">
                                <i class="bi bi-folder me-2"></i>Tài liệu của tôi
                            </a></li>
                            <li><a class="dropdown-item" href="<?= url('/favorites') ?>">
                                <i class="bi bi-bookmark-heart me-2"></i>Yêu thích
                            </a></li>
                            <li><a class="dropdown-item" href="<?= url('/history') ?>">
                                <i class="bi bi-clock-history me-2"></i>Lịch sử
                            </a></li>
                            <?php if (isModerator()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= url('/admin') ?>">
                                    <i class="bi bi-shield-check me-2"></i>Quản trị
                                </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="<?= url('/logout') ?>" method="POST">
                                    <?= csrfField() ?>
                                    <button class="dropdown-item text-danger" type="submit">
                                        <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-light btn-sm" href="<?= url('/login') ?>">Đăng nhập</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-light btn-sm text-primary fw-semibold" href="<?= url('/register') ?>">Đăng ký</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<div class="container mt-3" id="flash-container">
    <?php foreach (['success', 'error', 'warning', 'info'] as $type): ?>
        <?php foreach (getFlash($type) as $msg): ?>
            <?php $bsType = $type === 'error' ? 'danger' : $type; ?>
            <div class="alert alert-<?= $bsType ?> alert-dismissible fade show" role="alert">
                <?= $msg /* intentionally unescaped — set only by server */ ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <?php
    // Handle serialized errors flash
    $errorsFlash = getFlash('errors');
    foreach ($errorsFlash as $serialized):
        $errArr = unserialize($serialized, ['allowed_classes' => false]);
        if (is_array($errArr)):
    ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Vui lòng kiểm tra lại thông tin:</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($errArr as $field => $msgs): ?>
                    <?php foreach ((array)$msgs as $m): ?>
                        <li><?= e($m) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; endforeach; ?>
</div>

<!-- Main Content -->
<main>
    <?= $content ?>
</main>

<!-- Footer -->
<?php require APP_PATH . '/Views/partials/footer.php'; ?>

<!-- AI Chatbot (Bottom-Left) -->
<?php require APP_PATH . '/Views/partials/ai_chatbot.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/main.js') ?>"></script>
<script src="<?= asset('js/chatbot.js') ?>"></script>
<?php clearOldInput(); ?>
</body>
</html>
