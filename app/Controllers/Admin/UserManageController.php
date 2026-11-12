<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;

class UserManageController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireRole('admin');

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $search  = trim($_GET['q'] ?? '');
        $role    = trim($_GET['role'] ?? '');
        $status  = trim($_GET['status'] ?? '');

        $userModel = new User();
        $this->view('admin.users.index', [
            'title'  => 'Quản lý người dùng — Admin TMU',
            'users'  => $userModel->getAllWithPagination($page, $perPage, $search, $role, $status),
            'search' => $search,
            'role'   => $role,
            'status' => $status,
        ], 'admin');
    }

    public function show(array $params = []): void
    {
        $this->requireRole('admin');
        $userModel = new User();
        $user = $userModel->findById((int)$params['id']);
        if (!$user) {
            flash('error', 'Người dùng không tồn tại.');
            $this->redirect('/admin/users');
        }
        $this->view('admin.users.show', [
            'title'    => 'Chi tiết người dùng — Admin TMU',
            'viewUser' => $user,
        ], 'admin');
    }

    public function toggleStatus(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $userId    = (int)$params['id'];
        $userModel = new User();
        $user      = $userModel->findById($userId);

        if (!$user) {
            flash('error', 'Người dùng không tồn tại.');
            $this->redirect('/admin/users');
        }

        // Cannot lock yourself
        if ($userId === userId()) {
            flash('error', 'Không thể khóa tài khoản của chính bạn.');
            $this->redirect('/admin/users');
        }

        $newStatus = $user['status'] === 'active' ? 'locked' : 'active';
        $userModel->update($userId, ['status' => $newStatus]);

        flash('success', $newStatus === 'locked' ? 'Tài khoản đã bị khóa.' : 'Tài khoản đã được mở khóa.');
        $this->redirect('/admin/users');
    }

    public function setRole(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $userId = (int)$params['id'];
        $role   = $_POST['role'] ?? '';

        if (!in_array($role, ['admin', 'moderator', 'member'])) {
            flash('error', 'Vai trò không hợp lệ.');
            $this->redirect('/admin/users');
        }

        // Cannot change own role
        if ($userId === userId()) {
            flash('error', 'Không thể thay đổi vai trò của chính bạn.');
            $this->redirect('/admin/users');
        }

        $userModel = new User();
        $userModel->update($userId, ['role' => $role]);

        flash('success', 'Vai trò đã được cập nhật thành công.');
        $this->redirect('/admin/users');
    }
}

