<?php declare(strict_types=1); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <div class="d-flex align-items-center gap-4 mb-4">
                    <?php $avatarSrc = avatarUrl((int)$user['id'], $user['avatar'] ?? null); ?>
                    <?php if ($avatarSrc): ?>
                        <img src="<?= e($avatarSrc) ?>" class="avatar-lg rounded-circle" alt="<?= e($user['full_name']) ?>">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?= mb_substr($user['full_name'], 0, 1) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h3 class="fw-bold mb-1"><?= e($user['full_name']) ?></h3>
                        <div class="text-muted small mb-2">@<?= e($user['username']) ?> &bull; <?= e($user['email']) ?></div>
                        <span class="badge bg-primary"><?= e(strtoupper($user['role'])) ?></span>
                        <?php $isActive = ($user['status'] ?? '') === 'active'; ?>
                        <span class="badge bg-<?= $isActive ? 'success' : 'danger' ?>">
                            <?= $isActive ? 'Hoạt động' : 'Bị khóa' ?>
                        </span>
                    </div>
                </div>

                <div class="border-top pt-3">
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Mã sinh viên:</div>
                        <div class="col-sm-8 fw-semibold"><?= e($user['student_id'] ?: 'Chưa cập nhật') ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Tiểu sử:</div>
                        <div class="col-sm-8"><?= nl2br(e($user['bio'] ?: 'Chưa có thông tin tiểu sử.')) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Ngày tham gia:</div>
                        <div class="col-sm-8"><?= formatDate($user['created_at']) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Đăng nhập gần nhất:</div>
                        <div class="col-sm-8"><?= $user['last_login'] ? formatDate($user['last_login'], 'H:i d/m/Y') : 'Chưa có dữ liệu' ?></div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex gap-2">
                    <a href="<?= url('/profile/edit') ?>" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Chỉnh sửa hồ sơ
                    </a>
                    <a href="<?= url('/my-documents') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-folder me-1"></i>Tài liệu đã đăng
                    </a>
                    <a href="<?= url('/favorites') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-bookmark-heart me-1"></i>Yêu thích
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

