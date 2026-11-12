<?php declare(strict_types=1); ?>
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tất cả tài liệu</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 filter-sidebar">
            <div class="card border-0 shadow-sm p-3">
                <h5 class="fw-bold mb-3"><i class="bi bi-funnel me-1"></i>Bộ lọc tìm kiếm</h5>
                <form action="<?= url('/documents') ?>" method="GET">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Từ khóa</label>
                        <input type="text" name="q" class="form-control form-control-sm" value="<?= e($filters['query'] ?? '') ?>" placeholder="Tên tài liệu...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Khoa / Viện</label>
                        <select name="faculty" class="form-select form-select-sm" id="filter_faculty">
                            <option value="">-- Tất cả Khoa/Viện --</option>
                            <?php foreach ($faculties as $fac): ?>
                                <option value="<?= $fac['id'] ?>" <?= ($filters['faculty_id'] ?? 0) == $fac['id'] ? 'selected' : '' ?>>
                                    <?= e($fac['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Môn học</label>
                        <select name="subject" class="form-select form-select-sm" id="filter_subject">
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
                            <option value="popular" <?= ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Xem nhiều nhất</option>
                            <option value="downloads" <?= ($filters['sort'] ?? '') === 'downloads' ? 'selected' : '' ?>>Tải nhiều nhất</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold mb-2">
                        <i class="bi bi-filter me-1"></i>Áp dụng
                    </button>
                    <a href="<?= url('/documents') ?>" class="btn btn-outline-secondary btn-sm w-100">
                        Đặt lại
                    </a>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Danh sách tài liệu (<?= number_format($docs['total']) ?>)</h4>
                <a href="<?= url('/documents/create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-cloud-arrow-up me-1"></i>Đăng tài liệu
                </a>
            </div>

            <?php if (empty($docs['items'])): ?>
                <div class="card border-0 shadow-sm p-5 text-center text-muted">
                    <i class="bi bi-folder-x fs-1 mb-2"></i>
                    <p class="mb-0">Không tìm thấy tài liệu phù hợp với điều kiện lọc.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($docs['items'] as $doc): ?>
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
                <?php if ($docs['last_page'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $docs['last_page']; $i++): ?>
                                <li class="page-item <?= $i === $docs['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('/documents?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>">
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

