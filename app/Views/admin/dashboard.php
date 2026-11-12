<?php declare(strict_types=1); ?>

<!-- Stats cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-people fs-3 text-primary"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($userStats['total']) ?></div>
                    <div class="text-muted small">Người dùng</div>
                    <div class="text-success small">+<?= $userStats['this_month'] ?> tháng này</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 p-3">
                    <i class="bi bi-files fs-3 text-success"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($documentStats['approved']) ?></div>
                    <div class="text-muted small">Tài liệu công khai</div>
                    <div class="text-warning small"><?= $documentStats['pending'] ?> chờ duyệt</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 p-3">
                    <i class="bi bi-download fs-3 text-info"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($totalDownloads) ?></div>
                    <div class="text-muted small">Lượt tải xuống</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-diagram-3 fs-3 text-warning"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= $totalFaculties ?> Khoa</div>
                    <div class="text-muted small"><?= $totalSubjects ?> môn học</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick action links -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 fw-semibold pt-3">
                Thao tác nhanh
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= url('/admin/moderation') ?>" class="btn btn-warning fw-semibold">
                        <i class="bi bi-clipboard-check me-2"></i>
                        Kiểm duyệt tài liệu (<?= $documentStats['pending'] ?> chờ duyệt)
                    </a>
                    <a href="<?= url('/admin/categories') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-diagram-3 me-2"></i>Quản lý Khoa / Môn / Loại
                    </a>
                    <a href="<?= url('/admin/users') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-people me-2"></i>Quản lý người dùng
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 fw-semibold pt-3">Trạng thái hệ thống</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">Người dùng hoạt động</td>
                        <td class="fw-semibold text-success"><?= $userStats['active'] ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Người dùng bị khóa</td>
                        <td class="fw-semibold text-danger"><?= $userStats['locked'] ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tài liệu tổng cộng</td>
                        <td class="fw-semibold"><?= $documentStats['total'] ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tài liệu bị từ chối</td>
                        <td class="fw-semibold text-muted"><?= $documentStats['rejected'] ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tổng lượt tải</td>
                        <td class="fw-semibold"><?= number_format($documentStats['downloads']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

