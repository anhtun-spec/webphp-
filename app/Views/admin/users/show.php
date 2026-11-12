<?php declare(strict_types=1); ?>
<div class="mb-4">
    <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
    </a>
    <h4 class="fw-bold mb-1">Chi tiết người dùng: <?= e($viewUser['full_name']) ?></h4>
</div>

<div class="card border-0 shadow-sm p-4">
    <div class="row">
        <div class="col-md-6 mb-3">
            <span class="text-muted small">Họ và tên:</span>
            <div class="fw-bold fs-5"><?= e($viewUser['full_name']) ?></div>
        </div>
        <div class="col-md-6 mb-3">
            <span class="text-muted small">Tên đăng nhập:</span>
            <div class="fw-bold">@<?= e($viewUser['username']) ?></div>
        </div>
        <div class="col-md-6 mb-3">
            <span class="text-muted small">Email:</span>
            <div><?= e($viewUser['email']) ?></div>
        </div>
        <div class="col-md-6 mb-3">
            <span class="text-muted small">Mã sinh viên:</span>
            <div><?= e($viewUser['student_id'] ?: 'Chưa cập nhật') ?></div>
        </div>
        <div class="col-md-6 mb-3">
            <span class="text-muted small">Vai trò hiện tại:</span>
            <div><span class="badge bg-primary"><?= e(strtoupper($viewUser['role'])) ?></span></div>
        </div>
        <div class="col-md-6 mb-3">
            <span class="text-muted small">Trạng thái:</span>
            <div>
                <span class="badge bg-<?= $viewUser['status'] === 'active' ? 'success' : 'danger' ?>">
                    <?= $viewUser['status'] === 'active' ? 'Hoạt động' : 'Bị khóa' ?>
                </span>
            </div>
        </div>
        <div class="col-md-12 mb-3">
            <span class="text-muted small">Tiểu sử:</span>
            <div class="bg-light p-2 rounded"><?= nl2br(e($viewUser['bio'] ?: 'Chưa có tiểu sử.')) ?></div>
        </div>
        <div class="col-md-6">
            <span class="text-muted small">Ngày tham gia:</span>
            <div><?= formatDate($viewUser['created_at'], 'H:i d/m/Y') ?></div>
        </div>
        <div class="col-md-6">
            <span class="text-muted small">Đăng nhập lần cuối:</span>
            <div><?= $viewUser['last_login'] ? formatDate($viewUser['last_login'], 'H:i d/m/Y') : 'Chưa đăng nhập' ?></div>
        </div>
    </div>
</div>

