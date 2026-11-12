<?php declare(strict_types=1); ?>

<!-- Hero Section -->
<section class="hero-section text-center py-5">
    <div class="container">
        <h1 class="display-5 fw-bold mb-3">Kho Tài Liệu Học Tập TMU</h1>
        <p class="lead mb-4 mx-auto" style="max-width: 650px;">
            Hệ thống chia sẻ giáo trình, bài giảng, đề thi và tài liệu học tập dành riêng cho sinh viên Trường Đại học Thương mại.
        </p>

        <!-- Search box -->
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <form action="<?= url('/search') ?>" method="GET" class="input-group input-group-lg shadow-sm">
                    <input type="text" name="q" class="form-control" placeholder="Tìm kiếm theo tên môn, giáo trình, đề thi..." required>
                    <button class="btn btn-warning fw-bold px-4" type="submit">
                        <i class="bi bi-search me-1"></i>Tìm kiếm
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-4">
            <a href="<?= url('/documents/create') ?>" class="btn btn-outline-light me-2">
                <i class="bi bi-cloud-arrow-up me-1"></i>Đóng góp tài liệu
            </a>
            <a href="<?= url('/faculties') ?>" class="btn btn-light text-primary">
                <i class="bi bi-diagram-3 me-1"></i>Xem theo Khoa/Viện
            </a>
        </div>
    </div>
</section>

<div class="container py-5">
    <!-- Quick Faculties Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="bi bi-mortarboard text-primary me-2"></i>Khoa / Viện đào tạo</h3>
        <a href="<?= url('/faculties') ?>" class="text-decoration-none">Xem tất cả <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row g-3 mb-5">
        <?php foreach ($faculties as $f): ?>
            <div class="col-md-4 col-sm-6">
                <div class="card h-100 border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-4">
                            <i class="bi bi-book"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">
                                <a href="<?= url('/faculties/' . $f['id']) ?>" class="text-dark text-decoration-none stretched-link">
                                    <?= e($f['name']) ?>
                                </a>
                            </h6>
                            <span class="text-muted small">
                                <?= (int)($f['subject_count'] ?? 0) ?> môn học &bull; <?= (int)($f['document_count'] ?? 0) ?> tài liệu
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Documents -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="bi bi-clock-history text-success me-2"></i>Tài liệu mới đăng</h3>
        <a href="<?= url('/documents?sort=newest') ?>" class="text-decoration-none">Xem thêm <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row g-4 mb-5">
        <?php if (empty($recentDocuments)): ?>
            <div class="col-12 text-center text-muted py-4">Chưa có tài liệu nào được công khai.</div>
        <?php else: ?>
            <?php foreach ($recentDocuments as $doc): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card document-card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-light text-primary border badge-type"><?= e($doc['type_name'] ?? 'Tài liệu') ?></span>
                                <span class="text-muted small"><i class="bi bi-file-earmark-pdf"></i> <?= formatSize((int)$doc['file_size']) ?></span>
                            </div>
                            <h6 class="card-title fw-bold">
                                <a href="<?= url('/documents/' . $doc['id']) ?>" class="text-dark text-decoration-none">
                                    <?= e(truncate($doc['title'], 55)) ?>
                                </a>
                            </h6>
                            <p class="card-text text-muted small mb-2">
                                <i class="bi bi-journal-text me-1"></i><?= e($doc['subject_name'] ?? 'TMU') ?>
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

    <!-- Popular Documents -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="bi bi-fire text-danger me-2"></i>Tài liệu nổi bật (Lượt tải cao)</h3>
        <a href="<?= url('/documents?sort=downloads') ?>" class="text-decoration-none">Xem thêm <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row g-4">
        <?php if (empty($popularDocuments)): ?>
            <div class="col-12 text-center text-muted py-4">Chưa có dữ liệu tài liệu nổi bật.</div>
        <?php else: ?>
            <?php foreach ($popularDocuments as $doc): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card document-card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-light text-danger border badge-type"><?= e($doc['type_name'] ?? 'Tài liệu') ?></span>
                                <span class="text-muted small"><?= formatSize((int)$doc['file_size']) ?></span>
                            </div>
                            <h6 class="card-title fw-bold">
                                <a href="<?= url('/documents/' . $doc['id']) ?>" class="text-dark text-decoration-none">
                                    <?= e(truncate($doc['title'], 55)) ?>
                                </a>
                            </h6>
                            <p class="card-text text-muted small mb-2">
                                <i class="bi bi-journal-text me-1"></i><?= e($doc['subject_name'] ?? 'TMU') ?>
                            </p>
                            <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center small text-muted">
                                <span><i class="bi bi-eye me-1"></i><?= (int)$doc['view_count'] ?></span>
                                <span><i class="bi bi-download text-success me-1"></i><?= (int)$doc['download_count'] ?> lượt tải</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

