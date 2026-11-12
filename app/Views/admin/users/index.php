<?php declare(strict_types=1); ?>
<?php
$search = trim((string)($filters['search'] ?? $_GET['search'] ?? ''));
$role   = trim((string)($filters['role'] ?? $_GET['role'] ?? ''));
$status = trim((string)($filters['status'] ?? $_GET['status'] ?? ''));
$stats  = $stats ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-people-fill text-primary me-2"></i>Quản lý người dùng
            </h3>
            <p class="text-muted small mb-0">
                Quản lý tài khoản sinh viên, phân quyền kiểm duyệt và trạng thái hoạt động
            </p>
        </div>
    </div>

    <!-- Thống kê nhanh -->
    <?php if (!empty($stats)): ?>
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-3">
                    <div class="text-muted small">Tổng tài khoản</div>
                    <div class="fs-4 fw-bold text-primary"><?= (int)($stats['total'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-3">
                    <div class="text-muted small">Đang hoạt động</div>
                    <div class="fs-4 fw-bold text-success"><?= (int)($stats['active'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-3">
                    <div class="text-muted small">Đã bị khóa</div>
                    <div class="fs-4 fw-bold text-danger"><?= (int)($stats['locked'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-3">
                    <div class="text-muted small">Mới trong tháng</div>
                    <div class="fs-4 fw-bold text-info"><?= (int)($stats['this_month'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bộ lọc tìm kiếm -->
    <div class="card border-0 shadow-sm p-3 mb-4">
        <form action="<?= url('/admin/users') ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Tìm kiếm</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       value="<?= e($search) ?>" placeholder="Họ tên, email hoặc tên đăng nhập...">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Vai trò</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">-- Tất cả vai trò --</option>
                    <option value="member" <?= $role === 'member' ? 'selected' : '' ?>>Thành viên (Member)</option>
                    <option value="moderator" <?= $role === 'moderator' ? 'selected' : '' ?>>Kiểm duyệt viên (Moderator)</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Quản trị viên (Admin)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Trạng thái</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                    <option value="locked" <?= $status === 'locked' ? 'selected' : '' ?>>Bị khóa</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Lọc
                </button>
                <?php if ($search !== '' || $role !== '' || $status !== ''): ?>
                    <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary btn-sm" title="Xóa bộ lọc">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Bảng danh sách người dùng -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Người dùng</th>
                        <th>Mã SV</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users['items'])): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Không tìm thấy tài khoản nào phù hợp.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users['items'] as $u): ?>
                            <?php
                                $uid       = (int)$u['id'];
                                $isSelf    = ($uid === userId());
                                $isLocked  = ($u['status'] ?? '') === 'locked';
                                $avatarSrc = avatarUrl($uid, $u['avatar'] ?? null);
                            ?>
                            <tr>
                                <td class="text-muted small">#<?= $uid ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($avatarSrc): ?>
                                            <img src="<?= e($avatarSrc) ?>" class="rounded-circle" width="36" height="36"
                                                 style="object-fit: cover;" alt="<?= e($u['full_name']) ?>">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold"
                                                 style="width: 36px; height: 36px; font-size: 0.9rem;">
                                                <?= mb_substr((string)$u['full_name'], 0, 1) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold small">
                                                <?= e($u['full_name']) ?>
                                                <?php if ($isSelf): ?>
                                                    <span class="badge bg-info text-dark ms-1">Bạn</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                @<?= e($u['username']) ?> &bull; <?= e($u['email']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="small fw-semibold"><?= e($u['student_id'] ?: '—') ?></td>
                                <td>
                                    <?php if (!$isSelf && isAdmin()): ?>
                                        <form action="<?= url('/admin/users/' . $uid . '/role') ?>" method="POST" class="d-inline-flex gap-1">
                                            <?= csrfField() ?>
                                            <select name="role" class="form-select form-select-sm" style="width: auto;"
                                                    onchange="this.form.submit()">
                                                <option value="member" <?= ($u['role'] ?? '') === 'member' ? 'selected' : '' ?>>Member</option>
                                                <option value="moderator" <?= ($u['role'] ?? '') === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                                                <option value="admin" <?= ($u['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-primary"><?= e(strtoupper((string)$u['role'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $isLocked ? 'danger' : 'success' ?>">
                                        <?= $isLocked ? 'Bị khóa' : 'Hoạt động' ?>
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <?= formatDate((string)$u['created_at']) ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <?php if (!$isSelf): ?>
                                        <form action="<?= url('/admin/users/' . $uid . '/toggle') ?>"
                                            method="POST" class="d-inline">
                                            <?= csrfField() ?>
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-<?= $isLocked ? 'success' : 'danger' ?>"
                                                    onclick="return confirm('<?= $isLocked ? 'Mở khóa tài khoản này?' : 'Khóa tài khoản này?' ?>')">
                                                <i class="bi bi-<?= $isLocked ? 'unlock' : 'lock' ?> me-1"></i>
                                                <?= $isLocked ? 'Mở khóa' : 'Khóa' ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Phân trang -->
    <?php if (($users['last_page'] ?? 1) > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $users['last_page']; $i++): ?>
                    <?php
                        $query = http_build_query(array_filter([
                            'search' => $search,
                            'role'   => $role,
                            'status' => $status,
                            'page'   => $i,
                        ]));
                    ?>
                    <li class="page-item <?= $i === ($users['current_page'] ?? 1) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('/admin/users?' . $query) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>