<?php declare(strict_types=1); ?>
<div class="container py-4">
    <div class="mb-4">
        <h3 class="fw-bold mb-1"><i class="bi bi-bell text-warning me-2"></i>Thông báo của bạn</h3>
        <p class="text-muted small mb-0">Cập nhật về trạng thái duyệt tài liệu, bình luận và phản hồi hệ thống</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="list-group list-group-flush">
            <?php if (empty($notifications)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-bell-slash fs-1 mb-2"></i>
                    <p class="mb-0">Bạn không có thông báo nào mới.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <?php
                        $iconClass = match ($n['type'] ?? '') {
                            'approved' => 'bi-check-circle-fill text-success',
                            'rejected' => 'bi-x-circle-fill text-danger',
                            'hidden'   => 'bi-eye-slash-fill text-warning',
                            'comment'  => 'bi-chat-dots-fill text-primary',
                            default    => 'bi-info-circle-fill text-secondary',
                        };
                    ?>
                    <div class="list-group-item p-3 <?= empty($n['is_read']) ? 'bg-light' : '' ?>">
                        <div class="d-flex gap-3">
                            <div class="fs-4 pt-1">
                                <i class="bi <?= $iconClass ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="fw-bold mb-0">
                                        <?php if (empty($n['is_read'])): ?>
                                            <span class="badge bg-warning text-dark me-1">Mới</span>
                                        <?php endif; ?>
                                        <?php if (!empty($n['link'])): ?>
                                            <a href="<?= url($n['link']) ?>" class="text-dark text-decoration-none">
                                                <?= e($n['title']) ?>
                                            </a>
                                        <?php else: ?>
                                            <?= e($n['title']) ?>
                                        <?php endif; ?>
                                    </h6>
                                    <span class="text-muted small ms-2 text-nowrap" style="font-size: 0.75rem;">
                                        <?= formatDate($n['created_at'], 'H:i d/m/Y') ?>
                                    </span>
                                </div>
                                <p class="mb-0 text-secondary small"><?= nl2br(e($n['message'])) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>