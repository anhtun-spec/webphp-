<?php declare(strict_types=1); ?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Danh mục Khoa / Viện</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1">Khoa / Viện Đào Tạo TMU</h2>
            <p class="text-muted mb-0">Chọn Khoa/Viện để tra cứu các môn học và kho tài liệu tương ứng</p>
        </div>
        <?php if (isAdmin()): ?>
            <a href="<?= url('/admin/categories') ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-gear me-1"></i>Quản lý & Chỉnh sửa danh mục
            </a>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <?php foreach ($faculties as $f): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-3">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">
                                <a href="<?= url('/faculties/' . $f['id']) ?>" class="text-dark text-decoration-none stretched-link">
                                    <?= e($f['name']) ?>
                                </a>
                            </h5>
                            <span class="badge bg-light text-secondary border"><?= e($f['short_name'] ?? 'TMU') ?></span>
                        </div>
                    </div>
                    <div class="mt-auto border-top pt-2 d-flex justify-content-between text-muted small">
                        <span><i class="bi bi-book me-1"></i><?= (int)($f['subject_count'] ?? 0) ?> môn học</span>
                        <span><i class="bi bi-files me-1"></i><?= (int)($f['document_count'] ?? 0) ?> tài liệu</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

