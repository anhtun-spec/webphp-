<?php declare(strict_types=1); ?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= url('/faculties') ?>">Danh mục Khoa / Viện</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($faculty['name']) ?></li>
        </ol>
    </nav>

    <div class="bg-primary text-white rounded p-4 mb-4 shadow-sm d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1"><?= e($faculty['name']) ?></h2>
            <p class="mb-0 text-white-50">Danh sách môn học và tài liệu đào tạo</p>
        </div>
        <?php if (isAdmin()): ?>
            <a href="<?= url('/admin/categories#faculties') ?>" class="btn btn-outline-light btn-sm">
                <i class="bi bi-pencil me-1"></i>Chỉnh sửa Khoa này
            </a>
        <?php endif; ?>
    </div>

    <h4 class="fw-bold mb-3"><i class="bi bi-book text-primary me-2"></i>Các môn học thuộc Khoa/Viện (<?= count($subjects) ?>)</h4>

    <div class="row g-3">
        <?php if (empty($subjects)): ?>
            <div class="col-12 text-center text-muted py-5">
                Chưa có môn học nào được cập nhật trong đơn vị này.
            </div>
        <?php else: ?>
            <?php foreach ($subjects as $s): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-light text-primary border"><?= e($s['code'] ?? 'MÔN HỌC') ?></span>
                            <span class="text-muted small"><?= (int)($s['document_count'] ?? 0) ?> tài liệu</span>
                        </div>
                        <h5 class="fw-bold mb-2">
                            <a href="<?= url('/subjects/' . $s['id']) ?>" class="text-dark text-decoration-none stretched-link">
                                <?= e($s['name']) ?>
                            </a>
                        </h5>
                        <p class="text-muted small mb-0">
                            <?= e($s['description'] ?: 'Tài liệu học tập, bài giảng, đề thi môn ' . $s['name']) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

