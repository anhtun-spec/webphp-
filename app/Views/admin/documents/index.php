<?php declare(strict_types=1); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-files text-primary me-2"></i>Tất cả tài liệu hệ thống</h4>
        <p class="text-muted small mb-0">Quản lý toàn bộ tài liệu đã đăng tải trên website</p>
    </div>
</div>

<!-- Filter -->
<div class="card border-0 shadow-sm p-3 mb-4">
    <form action="<?= url('/admin/documents') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-md-6">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Tìm kiếm theo tiêu đề tài liệu..." value="<?= e($filters['query'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select form-select-sm">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Đã duyệt (Công khai)</option>
                <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Bị từ chối</option>
                <option value="hidden" <?= ($filters['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Đã ẩn</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Lọc tài liệu</button>
        </div>
    </form>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tiêu đề</th>
                    <th>Người đăng</th>
                    <th>Môn học</th>
                    <th>Loại</th>
                    <th>Trạng thái</th>
                    <th>Lượt tải</th>
                    <th>Ngày đăng</th>
                    <th class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($docs['items'])): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">Không tìm thấy tài liệu nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($docs['items'] as $doc): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/documents/' . $doc['id']) ?>" target="_blank" class="fw-bold text-dark text-decoration-none">
                                    <?= e(truncate($doc['title'], 45)) ?>
                                </a>
                            </td>
                            <td><?= e($doc['uploader_name'] ?? '—') ?></td>
                            <td class="small text-muted"><?= e($doc['subject_name'] ?? '—') ?></td>
                            <td><span class="badge bg-light text-secondary border"><?= e($doc['type_name'] ?? 'Tài liệu') ?></span></td>
                            <td>
                                <?php $stInfo = documentStatusInfo((string)$doc['status']); ?>
                                <span class="badge <?= $stInfo['bs'] ?>"><?= e($stInfo['label']) ?></span>
                            </td>
                            <td><?= (int)$doc['download_count'] ?></td>
                            <td class="small text-muted"><?= formatDate($doc['created_at']) ?></td>
                            <td class="text-end">
                                <a href="<?= url('/documents/' . $doc['id']) ?>" class="btn btn-sm btn-outline-info" title="Xem" target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($doc['status'] === 'approved'): ?>
                                    <form action="<?= url('/admin/documents/' . $doc['id'] . '/hide') ?>" method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Ẩn tài liệu">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    </form>
                                <?php elseif ($doc['status'] === 'hidden'): ?>
                                    <form action="<?= url('/admin/documents/' . $doc['id'] . '/unhide') ?>" method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Hiện lại tài liệu">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if (isAdmin()): ?>
                                    <form action="<?= url('/admin/documents/' . $doc['id'] . '/delete') ?>" method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa" data-confirm="Xác nhận xóa tài liệu này?">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($docs['last_page'] > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $docs['last_page']; $i++): ?>
                <li class="page-item <?= $i === $docs['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= url('/admin/documents?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

