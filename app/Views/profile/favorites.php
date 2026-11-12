<?php declare(strict_types=1); ?>
<div class="container py-4">
    <div class="mb-4">
        <h3 class="fw-bold mb-1"><i class="bi bi-bookmark-heart text-danger me-2"></i>Tài liệu đã lưu / yêu thích</h3>
        <p class="text-muted small mb-0">Danh sách các tài liệu học tập bạn đã đánh dấu để tra cứu nhanh</p>
    </div>

    <div class="row g-3" id="favorites-grid">
        <?php if (empty($favorites['items'])): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm p-5 text-center text-muted">
                    <i class="bi bi-bookmark-x fs-1 mb-2"></i>
                    <p class="mb-0">Bạn chưa lưu tài liệu nào. Khi xem tài liệu, bấm <strong>Lưu tài liệu</strong> để xem lại tại đây.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($favorites['items'] as $doc): ?>
                <div class="col-md-6 col-lg-4 favorite-item-col">
                    <div class="card document-card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column">
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
                                <span><i class="bi bi-calendar3 me-1"></i>Lưu: <?= formatDate($doc['favorited_at']) ?></span>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-favorite p-1" data-id="<?= (int)$doc['id'] ?>" title="Bỏ lưu">
                                    <i class="bi bi-bookmark-heart-fill text-danger"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (($favorites['last_page'] ?? 1) > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $favorites['last_page']; $i++): ?>
                    <li class="page-item <?= $i === ($favorites['current_page'] ?? 1) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('/favorites?page=' . $i) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('favorites-grid');
    if (!grid) return;

    // Tự động ẩn thẻ card khỏi danh sách khi icon trái tim chuyển sang trạng thái bỏ lưu
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(m) {
            if (m.type === 'attributes' && m.target.tagName === 'I') {
                const icon = m.target;
                if (icon.classList.contains('bi-bookmark-heart') && !icon.classList.contains('bi-bookmark-heart-fill')) {
                    const col = icon.closest('.favorite-item-col');
                    if (col) {
                        col.remove();
                        if (grid.querySelectorAll('.favorite-item-col').length === 0) {
                            grid.innerHTML = '<div class="col-12"><div class="card border-0 shadow-sm p-5 text-center text-muted"><i class="bi bi-bookmark-x fs-1 mb-2"></i><p class="mb-0">Bạn chưa lưu tài liệu nào. Khi xem tài liệu, bấm <strong>Lưu tài liệu</strong> để xem lại tại đây.</p></div></div>';
                        }
                    }
                }
            }
        });
    });

    observer.observe(grid, { subtree: true, attributes: true, attributeFilter: ['class'] });
});
</script>