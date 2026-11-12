<?php declare(strict_types=1); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-bar-chart text-primary me-2"></i>Thống kê hệ thống TMU</h4>
        <p class="text-muted small mb-0">Báo cáo tổng hợp số liệu người dùng, tài liệu và hoạt động tải xuống</p>
    </div>
</div>

<!-- Stats row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="text-muted small">Tổng người dùng</div>
            <div class="fs-3 fw-bold text-primary"><?= number_format($userStats['total']) ?></div>
            <div class="small text-success">+<?= $userStats['this_month'] ?> tháng này</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="text-muted small">Tài liệu công khai</div>
            <div class="fs-3 fw-bold text-success"><?= number_format($documentStats['approved']) ?></div>
            <div class="small text-warning"><?= $documentStats['pending'] ?> chờ duyệt</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="text-muted small">Lượt tải tài liệu</div>
            <div class="fs-3 fw-bold text-info"><?= number_format($totalDownloads) ?></div>
            <div class="small text-muted">Lịch sử lượt tải thực tế</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="text-muted small">Khoa & Môn học</div>
            <div class="fs-3 fw-bold text-warning"><?= $totalFaculties ?> Khoa</div>
            <div class="small text-muted"><?= $totalSubjects ?> môn học</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Top 10 most downloaded documents -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-3 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-fire text-danger me-2"></i>Top 10 tài liệu được tải nhiều nhất</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tên tài liệu</th>
                            <th>Môn</th>
                            <th class="text-end">Lượt tải</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topDownloads)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                        <?php else: ?>
                            <?php foreach ($topDownloads as $idx => $d): ?>
                                <tr>
                                    <td><strong><?= $idx + 1 ?></strong></td>
                                    <td>
                                        <a href="<?= url('/documents/' . $d['id']) ?>" target="_blank" class="text-dark fw-semibold text-decoration-none small">
                                            <?= e(truncate($d['title'], 35)) ?>
                                        </a>
                                    </td>
                                    <td class="small text-muted"><?= e($d['subject_name'] ?? '') ?></td>
                                    <td class="text-end fw-bold text-success"><?= number_format((int)$d['download_count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top uploaders -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-3 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-trophy text-warning me-2"></i>Top thành viên tích cực đóng góp</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Thành viên</th>
                            <th>Số tài liệu</th>
                            <th class="text-end">Tổng lượt tải</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topUploaders)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                        <?php else: ?>
                            <?php foreach ($topUploaders as $idx => $u): ?>
                                <tr>
                                    <td><strong><?= $idx + 1 ?></strong></td>
                                    <td>
                                        <div class="fw-semibold small"><?= e($u['full_name']) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem;">@<?= e($u['username']) ?></div>
                                    </td>
                                    <td class="fw-bold text-primary"><?= (int)$u['doc_count'] ?></td>
                                    <td class="text-end fw-bold text-success"><?= number_format((int)$u['total_downloads']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Documents by Faculty -->
<div class="card border-0 shadow-sm p-3">
    <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart text-primary me-2"></i>Phân bố tài liệu theo Khoa / Viện</h5>
    <div class="row g-3">
        <?php foreach ($byFaculty as $f): ?>
            <div class="col-md-4 col-sm-6">
                <div class="border rounded p-3 bg-light">
                    <div class="fw-bold small"><?= e($f['faculty_name']) ?></div>
                    <div class="fs-4 fw-bold text-primary mt-1"><?= number_format((int)$f['doc_count']) ?> <span class="fs-6 text-muted font-normal">tài liệu</span></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

