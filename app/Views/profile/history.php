<?php declare(strict_types=1); ?>
<div class="container py-4">
    <div class="mb-4">
        <h3 class="fw-bold mb-1"><i class="bi bi-clock-history text-info me-2"></i>Lịch sử tải xuống</h3>
        <p class="text-muted small mb-0">Các tài liệu học tập bạn đã tải về máy tính hoặc điện thoại</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tài liệu</th>
                        <th>Môn học</th>
                        <th>Loại</th>
                        <th>Thời gian tải</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history['items'])): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Chưa có lịch sử tải xuống tài liệu.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history['items'] as $item): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('/documents/' . $item['document_id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= e($item['title']) ?>
                                    </a>
                                </td>
                                <td><?= e($item['subject_name'] ?? 'Chưa rõ') ?></td>
                                <td>
                                    <span class="badge bg-light text-secondary border"><?= e($item['type_name'] ?? 'Tài liệu') ?></span>
                                </td>
                                <td class="text-muted small"><?= formatDate($item['downloaded_at'], 'H:i d/m/Y') ?></td>
                                <td class="text-end">
                                    <a href="<?= url('/documents/' . $item['document_id'] . '/download') ?>" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-download me-1"></i>Tải lại
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($history['last_page'] > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $history['last_page']; $i++): ?>
                    <li class="page-item <?= $i === $history['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('/history?page=' . $i) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

