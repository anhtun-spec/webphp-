<?php declare(strict_types=1); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-folder text-primary me-2"></i>Tài liệu của tôi</h3>
            <p class="text-muted small mb-0">Quản lý và theo dõi trạng thái các tài liệu bạn đã đóng góp</p>
        </div>
        <a href="<?= url('/documents/create') ?>" class="btn btn-primary">
            <i class="bi bi-cloud-arrow-up me-1"></i>Đăng tài liệu mới
        </a>
    </div>
<?php
$currentStatus = $status ?? '';
$counts        = $statusCounts ?? [];
$tabs          = [
    ''         => ['label' => 'Tất cả',     'count' => $counts['all'] ?? null],
    'approved' => ['label' => 'Đã duyệt',   'count' => $counts['approved'] ?? null],
    'pending'  => ['label' => 'Chờ duyệt',  'count' => $counts['pending'] ?? null],
    'rejected' => ['label' => 'Bị từ chối', 'count' => $counts['rejected'] ?? null],
    'hidden'   => ['label' => 'Đã ẩn',      'count' => $counts['hidden'] ?? null],
];
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <?php foreach ($tabs as $key => $tab): ?>
        <a href="<?= url('/my-documents' . ($key !== '' ? '?status=' . $key : '')) ?>"
           class="btn btn-sm <?= $currentStatus === $key ? 'btn-primary fw-semibold' : 'btn-outline-secondary' ?>">
            <?= e($tab['label']) ?>
            <?php if ($tab['count'] !== null): ?>
                <span class="badge <?= $currentStatus === $key ? 'bg-light text-primary' : 'bg-secondary' ?> ms-1">
                    <?= (int)$tab['count'] ?>
                </span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tên tài liệu</th>
                        <th>Khoa / Môn học</th>
                        <th>Loại</th>
                        <th>Trạng thái</th>
                        <th>Thống kê</th>
                        <th>Ngày gửi</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($docs['items'])): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <?= $currentStatus !== '' ? 'Không có tài liệu nào ở trạng thái này.' : 'Bạn chưa đăng tải tài liệu nào. Hãy bắt đầu chia sẻ ngay!' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($docs['items'] as $doc): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('/documents/' . $doc['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= e(truncate($doc['title'], 50)) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><?= e($doc['subject_name'] ?? 'Chưa rõ') ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= e($doc['faculty_name'] ?? '') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border"><?= e($doc['type_name'] ?? 'Tài liệu') ?></span>
                                </td>
                                <td>
                                    <?php $stInfo = documentStatusInfo((string)$doc['status']); ?>
                                    <span class="badge <?= $stInfo['badge'] ?>"><?= e($stInfo['label']) ?></span>
                                    <?php if ($doc['status'] === 'rejected' && !empty($doc['rejection_reason'])): ?>
                                        <div class="mt-1">
                                            <button type="button" class="btn btn-outline-danger btn-xs py-0 px-2" style="font-size: 0.72rem;" data-bs-toggle="modal" data-bs-target="#reasonModal-<?= $doc['id'] ?>" title="Xem lý do từ chối">
                                                <i class="bi bi-exclamation-circle me-1"></i>Lý do từ chối
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <i class="bi bi-eye"></i> <?= (int)$doc['view_count'] ?> &bull;
                                    <i class="bi bi-download"></i> <?= (int)$doc['download_count'] ?>
                                </td>
                                <td class="small text-muted"><?= formatDate($doc['created_at']) ?></td>
                                <td class="text-end">
                                    <a href="<?= url('/documents/' . $doc['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?= url('/documents/' . $doc['id'] . '/delete') ?>" method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa" data-confirm="Bạn có chắc chắn muốn xóa tài liệu này?">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
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
                        <a class="page-link" href="<?= url('/my-documents?' . http_build_query(array_filter(['status' => $currentStatus, 'page' => $i]))) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Modals hiển thị lý do từ chối -->
<?php if (!empty($docs['items'])): ?>
    <?php foreach ($docs['items'] as $doc): ?>
        <?php if ($doc['status'] === 'rejected' && !empty($doc['rejection_reason'])): ?>
            <div class="modal fade" id="reasonModal-<?= $doc['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom bg-danger bg-opacity-10">
                            <h6 class="modal-title fw-bold text-danger mb-0">
                                <i class="bi bi-x-octagon-fill me-2"></i>Lý do tài liệu bị từ chối
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-3">
                            <p class="small text-muted mb-2">Tài liệu: <strong><?= e($doc['title']) ?></strong></p>
                            <div class="p-3 bg-light rounded border border-danger-subtle text-danger-emphasis">
                                <i class="bi bi-chat-left-quote me-1"></i>
                                <?= nl2br(e($doc['rejection_reason'])) ?>
                            </div>
                            <p class="text-muted small mt-3 mb-0">
                                <i class="bi bi-lightbulb me-1"></i>Bạn có thể bấm nút <strong>Chỉnh sửa tài liệu ngay</strong> bên dưới để cập nhật lại thông tin hoặc thay thế tệp và gửi lại cho kiểm duyệt viên.
                            </p>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <a href="<?= url('/documents/' . $doc['id'] . '/edit') ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil me-1"></i>Chỉnh sửa tài liệu ngay
                            </a>
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

