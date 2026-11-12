<?php declare(strict_types=1); ?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tìm kiếm</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Search filters sidebar -->
        <div class="col-lg-3 filter-sidebar">
            <div class="card border-0 shadow-sm p-3">
                <h5 class="fw-bold mb-3"><i class="bi bi-sliders me-1"></i>Bộ lọc tìm kiếm</h5>
                <form action="<?= url('/search') ?>" method="GET">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Từ khóa</label>
                        <input type="text" name="q" class="form-control form-control-sm" value="<?= e($filters['query'] ?? '') ?>" placeholder="Nhập tên tài liệu...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Khoa / Viện</label>
                        <select name="faculty" class="form-select form-select-sm">
                            <option value="">-- Tất cả Khoa --</option>
                            <?php foreach ($faculties as $fac): ?>
                                <option value="<?= $fac['id'] ?>" <?= ($filters['faculty_id'] ?? 0) == $fac['id'] ? 'selected' : '' ?>>
                                    <?= e($fac['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Môn học</label>
                        <select name="subject" class="form-select form-select-sm">
                            <option value="">-- Tất cả môn học --</option>
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?= $sub['id'] ?>" <?= ($filters['subject_id'] ?? 0) == $sub['id'] ? 'selected' : '' ?>>
                                    <?= e($sub['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Loại tài liệu</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">-- Tất cả loại --</option>
                            <?php foreach ($types as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($filters['type_id'] ?? 0) == $t['id'] ? 'selected' : '' ?>>
                                    <?= e($t['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sắp xếp</label>
                        <select name="sort" class="form-select form-select-sm">
                            <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="popular" <?= ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Xem nhiều</option>
                            <option value="downloads" <?= ($filters['sort'] ?? '') === 'downloads' ? 'selected' : '' ?>>Tải nhiều</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                        <i class="bi bi-search me-1"></i>Tìm kiếm
                    </button>
                </form>
            </div>
        </div>

        <!-- Search results list -->
        <div class="col-lg-9">
            <h4 class="fw-bold mb-3">
                Kết quả tìm kiếm <?= $filters['query'] ? 'cho: "' . e($filters['query']) . '"' : '' ?>
                <span class="badge bg-secondary fs-6"><?= number_format($results['total']) ?> kết quả</span>
            </h4>

            <?php if (empty($results['items'])): ?>
                <div class="card border-0 shadow-sm p-5 text-center text-muted">
                    <i class="bi bi-search fs-1 mb-2"></i>
                    <h5>Không tìm thấy tài liệu phù hợp</h5>
                    <p class="small mb-0">Hãy thử tìm với từ khóa chung hơn hoặc chọn bộ lọc Khoa/Môn khác.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($results['items'] as $doc): ?>
                        <div class="col-md-6 col-xl-4">
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
                </div>

                <!-- Pagination -->
                <?php if ($results['last_page'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $results['last_page']; $i++): ?>
                                <li class="page-item <?= $i === $results['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('/search?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

