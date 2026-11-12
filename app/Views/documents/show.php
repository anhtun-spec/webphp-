<?php declare(strict_types=1); ?>
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= url('/faculties/' . $doc['faculty_id']) ?>"><?= e($doc['faculty_name'] ?? 'Khoa') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= url('/subjects/' . $doc['subject_id']) ?>"><?= e($doc['subject_name'] ?? 'Môn học') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e(truncate($doc['title'], 40)) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left details column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-primary fs-6"><?= e($doc['type_name'] ?? 'Tài liệu') ?></span>
                    <?php if ($doc['status'] !== 'approved'): ?>
                        <?php $stInfo = documentStatusInfo((string)$doc['status']); ?>
                        <span class="badge <?= $stInfo['bs'] ?> fs-6">Trạng thái: <?= e($stInfo['label']) ?></span>
                    <?php endif; ?>
                </div>

                <h3 class="fw-bold mb-3"><?= e($doc['title']) ?></h3>

                <div class="d-flex flex-wrap gap-3 text-muted small border-bottom pb-3 mb-3">
                    <span><i class="bi bi-person me-1"></i>Đăng bởi: <strong><?= e($doc['uploader_name'] ?? 'Thành viên TMU') ?></strong></span>
                    <span><i class="bi bi-calendar3 me-1"></i><?= formatDate($doc['created_at']) ?></span>
                    <span><i class="bi bi-eye me-1"></i><?= (int)$doc['view_count'] ?> lượt xem</span>
                    <span><i class="bi bi-download me-1"></i><?= (int)$doc['download_count'] ?> lượt tải</span>
                </div>

                <!-- Description -->
                <h5 class="fw-bold mt-2">Mô tả tài liệu</h5>
                <div class="bg-light p-3 rounded mb-4">
                    <?= nl2br(e($doc['description'] ?: 'Chưa có mô tả chi tiết cho tài liệu này.')) ?>
                </div>

                <!-- PDF Preview -->
                <?php if ($doc['mime_type'] === 'application/pdf'): ?>
                    <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Xem trước trực tiếp</h5>
                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm border mb-4" style="min-height: 550px;">
                        <iframe src="<?= url('/documents/' . $doc['id'] . '/preview') ?>" allowfullscreen></iframe>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info d-flex align-items-center mb-4">
                        <i class="bi bi-info-circle fs-4 me-3"></i>
                        <div>
                            Tài liệu định dạng <strong><?= e(pathinfo($doc['original_filename'], PATHINFO_EXTENSION)) ?></strong>. Vui lòng bấm <strong>Tải xuống</strong> để xem nội dung đầy đủ trên máy tính của bạn.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Actions: Download & Favorite -->
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <a href="<?= url('/documents/' . $doc['id'] . '/download') ?>" class="btn btn-success btn-lg px-4 fw-bold">
                        <i class="bi bi-download me-2"></i>Tải xuống (<?= formatSize((int)$doc['file_size']) ?>)
                    </a>

                    <?php if (auth()): ?>
                        <button class="btn btn-outline-danger btn-lg px-3 btn-favorite" data-id="<?= $doc['id'] ?>" title="<?= $isFavorited ? 'Bỏ yêu thích' : 'Thêm vào yêu thích' ?>">
                            <i class="bi bi-bookmark-heart<?= $isFavorited ? '-fill' : '' ?> me-1"></i>
                            <span class="favorite-label"><?= $isFavorited ? 'Đã lưu' : 'Lưu tài liệu' ?></span>
                        </button>
                    <?php else: ?>
                        <a href="<?= url('/login') ?>" class="btn btn-outline-secondary btn-lg px-3">
                            <i class="bi bi-bookmark-heart me-1"></i>Đăng nhập để lưu
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Comments Section -->
                <hr>
                <div id="comments" class="mt-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-chat-dots me-2"></i>Bình luận (<?= count($comments) ?>)</h5>

                    <?php if (auth()): ?>
                        <form action="<?= url('/documents/' . $doc['id'] . '/comment') ?>" method="POST" class="mb-4">
                            <?= csrfField() ?>
                            <div class="mb-2">
                                <textarea name="content" class="form-control" rows="3" placeholder="Viết câu hỏi hoặc thảo luận về tài liệu này..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                                <i class="bi bi-send me-1"></i>Gửi bình luận
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-light border small text-muted mb-4">
                            Vui lòng <a href="<?= url('/login') ?>" class="fw-semibold">đăng nhập</a> để tham gia bình luận.
                        </div>
                    <?php endif; ?>

                    <!-- Comments list -->
                    <?php if (empty($comments)): ?>
                        <p class="text-muted small">Chưa có bình luận nào. Hãy là người đầu tiên thảo luận!</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php
                            $commentModel = new \App\Models\Comment();
                            foreach ($comments as $c):
                                $replies = $commentModel->getReplies((int)$c['id']);
                            ?>
                                <div class="list-group-item px-0 py-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <strong><?= e($c['full_name']) ?></strong>
                                            <span class="text-muted small"><?= formatDate($c['created_at'], 'H:i d/m/Y') ?></span>
                                        </div>
                                        <?php if (auth() && (userId() === (int)$c['user_id'] || isModerator())): ?>
                                            <form action="<?= url('/comments/' . $c['id'] . '/delete') ?>" method="POST" class="d-inline">
                                                <?= csrfField() ?>
                                                <button type="submit" class="btn btn-sm text-danger p-0 border-0" data-confirm="Xóa bình luận này?">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-2"><?= nl2br(e($c['content'])) ?></p>

                                    <!-- Reply form button -->
                                    <?php if (auth()): ?>
                                        <a href="#reply-form-<?= $c['id'] ?>" class="small text-muted text-decoration-none" data-bs-toggle="collapse">
                                            <i class="bi bi-reply"></i> Trả lời
                                        </a>
                                        <div class="collapse mt-2" id="reply-form-<?= $c['id'] ?>">
                                            <form action="<?= url('/documents/' . $doc['id'] . '/comment') ?>" method="POST">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="parent_id" value="<?= $c['id'] ?>">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="content" class="form-control" placeholder="Viết phản hồi..." required>
                                                    <button class="btn btn-primary" type="submit">Gửi</button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Nested replies -->
                                    <?php if (!empty($replies)): ?>
                                        <div class="comment-replies ms-2 ms-sm-4 mt-3 border-start ps-2 ps-sm-3">
                                            <?php foreach ($replies as $r): ?>
                                                <div class="mb-2">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <span class="fw-semibold small"><?= e($r['full_name']) ?></span>
                                                            <span class="text-muted" style="font-size: 0.75rem;"><?= formatDate($r['created_at'], 'H:i d/m/Y') ?></span>
                                                        </div>
                                                        <?php if (auth() && (userId() === (int)$r['user_id'] || isModerator())): ?>
                                                            <form action="<?= url('/comments/' . $r['id'] . '/delete') ?>" method="POST" class="d-inline">
                                                                <?= csrfField() ?>
                                                                <button type="submit" class="btn btn-sm text-danger p-0 border-0" data-confirm="Xóa phản hồi này?">
                                                                    <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="mb-1 small text-break"><?= nl2br(e($r['content'])) ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right metadata column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-3 mb-4">
                <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-info-circle me-1"></i>Thông tin học liệu</h6>
                <table class="table table-sm table-borderless small mb-0">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Khoa / Viện:</td>
                        <td class="fw-semibold"><?= e($doc['faculty_name'] ?? 'TMU') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Môn học:</td>
                        <td class="fw-semibold"><?= e($doc['subject_name'] ?? 'Chưa rõ') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Loại:</td>
                        <td><span class="badge bg-light text-dark border"><?= e($doc['type_name'] ?? 'Tài liệu') ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tên file gốc:</td>
                        <td class="text-break"><?= e($doc['original_filename']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dung lượng:</td>
                        <td><?= formatSize((int)$doc['file_size']) ?></td>
                    </tr>
                </table>
            </div>

            <?php if (auth() && (userId() === (int)$doc['user_id'] || isAdmin())): ?>
                <div class="card border-0 shadow-sm p-3">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">Tác vụ quản lý</h6>
                    <div class="d-grid gap-2">
                        <a href="<?= url('/documents/' . $doc['id'] . '/edit') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>Chỉnh sửa tài liệu
                        </a>
                        <form action="<?= url('/documents/' . $doc['id'] . '/delete') ?>" method="POST" class="d-grid">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="Bạn có chắc chắn muốn xóa tài liệu này không?">
                                <i class="bi bi-trash me-1"></i>Xóa tài liệu
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.comment-replies {
    max-width: 100%;
}
@media (max-width: 575.98px) {
    .comment-replies {
        margin-left: 0.5rem !important;
        padding-left: 0.5rem !important;
    }
}
</style>

<script>
    window.TMU_DOC_CONTEXT = {
        id: <?= (int)$doc['id'] ?>,
        title: <?= json_encode($doc['title'], JSON_UNESCAPED_UNICODE) ?>
    };
</script>


