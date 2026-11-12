<?php declare(strict_types=1); ?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= url('/faculties/' . $subject['faculty_id']) ?>"><?= e($subject['faculty_name']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($subject['name']) ?></li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="badge bg-primary mb-2"><?= e($subject['code'] ?? 'MÃ MÔN') ?></span>
                <h2 class="fw-bold mb-1"><?= e($subject['name']) ?></h2>
                <p class="text-muted small mb-0"><?= e($subject['faculty_name']) ?> &bull; <?= e($subject['university_name'] ?? 'TMU') ?></p>
            </div>
            <div class="d-flex gap-2">
                <?php if (isAdmin()): ?>
                    <a href="<?= url('/admin/categories#subjects') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-pencil me-1"></i>Chỉnh sửa môn
                    </a>
                <?php endif; ?>
                <a href="<?= url('/documents/create') ?>" class="btn btn-outline-primary">
                    <i class="bi bi-cloud-arrow-up me-1"></i>Đăng tài liệu môn này
                </a>
            </div>
        </div>
    </div>

    <!-- Filter by document type -->
    <div class="d-flex gap-2 overflow-auto pb-2 mb-4">
        <a href="<?= url('/subjects/' . $subject['id']) ?>"
           class="btn btn-sm <?= empty($filters['type_id']) ? 'btn-primary' : 'btn-outline-secondary' ?>">
            Tất cả loại
        </a>
        <?php foreach ($types as $t): ?>
            <a href="<?= url('/subjects/' . $subject['id'] . '?type=' . $t['id']) ?>"
               class="btn btn-sm <?= ($filters['type_id'] ?? 0) == $t['id'] ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <?= e($t['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Documents list -->
    <div class="row g-3">
        <?php if (empty($docs['items'])): ?>
            <div class="col-12 text-center text-muted py-5">
                Chưa có tài liệu nào thuộc môn học này. Hãy là người đầu tiên chia sẻ!
            </div>
        <?php else: ?>
            <?php foreach ($docs['items'] as $doc): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card document-card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-light text-primary border badge-type"><?= e($doc['type_name'] ?? 'Tài liệu') ?></span>
                                <span class="text-muted small"><?= formatSize((int)$doc['file_size']) ?></span>
                            </div>
                            <h6 class="card-title fw-bold">
                                <a href="<?= url('/documents/' . $doc['id']) ?>" class="text-dark text-decoration-none">
                                    <?= e(truncate($doc['title'], 55)) ?>
                                </a>
                            </h6>
                            <p class="card-text text-muted small mb-2">
                                <?= e(truncate($doc['description'] ?? '', 80)) ?>
                            </p>
                            <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center small text-muted">
                                <span><i class="bi bi-eye me-1"></i><?= (int)$doc['view_count'] ?></span>
                                <span><i class="bi bi-download me-1"></i><?= (int)$doc['download_count'] ?> lượt tải</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($docs['last_page'] > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $docs['last_page']; $i++): ?>
                    <li class="page-item <?= $i === $docs['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('/subjects/' . $subject['id'] . '?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

