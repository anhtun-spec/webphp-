<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;
use App\Models\User;
use App\Models\Document;
use App\Models\Favorite;
use App\Models\DownloadHistory;
use App\Models\Notification;
use App\Services\FileService;
use App\Services\AuthService;

class ProfileController extends Controller
{
    public function show(array $params = []): void
    {
        $this->requireLogin();
        $userModel = new User();
        $user      = $userModel->findById(userId());
        $this->view('profile.show', [
            'title' => 'Hồ sơ cá nhân — TMU Tài Liệu',
            'user'  => $user,
        ]);
    }

    public function editForm(array $params = []): void
    {
        $this->requireLogin();
        $userModel = new User();
        $this->view('profile.edit', [
            'title' => 'Chỉnh sửa hồ sơ — TMU Tài Liệu',
            'user'  => $userModel->findById(userId()),
        ]);
    }

    public function update(array $params = []): void
    {
        $this->requireLogin();
        $this->csrfValidate();

        $userModel   = new User();
        $currentUser = $userModel->findById(userId());

        $rawStudentId = strtoupper(trim($_POST['student_id'] ?? ''));
        $data = [
            'full_name'  => trim($_POST['full_name'] ?? ''),
            'student_id' => $rawStudentId,
            'bio'        => trim($_POST['bio'] ?? ''),
        ];

        $studentRule = (($currentUser['role'] ?? 'member') === 'member')
            ? 'required|tmu_student_id'
            : 'nullable|tmu_student_id';

        $errors = $this->validate($data, [
            'full_name'  => 'required|min:2|max:100|vietnamese_name',
            'student_id' => $studentRule,
            'bio'        => 'max:1000',
        ]);

        if ($rawStudentId !== '' && empty($errors['student_id']) && $userModel->studentIdExists($rawStudentId, userId())) {
            $errors['student_id'][] = 'Mã sinh viên này đã thuộc về một tài khoản khác.';
        }

        if (!empty($errors)) {
            storeOldInput();
            $_SESSION['old_input']['student_id'] = $rawStudentId;
            flash('errors', serialize($errors));
            $this->redirect('/profile/edit');
        }

        // Ép chuỗi rỗng thành NULL để không vi phạm UNIQUE KEY uk_student_id
        $data['student_id'] = $rawStudentId !== '' ? $rawStudentId : null;

        if (!empty($_FILES['avatar']['name'])) {
            $fileService = new FileService();
            $result = $fileService->uploadAvatar($_FILES['avatar']);
            if ($result['success']) {
                $data['avatar'] = $result['filename'];
                if (!empty($currentUser['avatar'])) {
                    $oldPath = STORAGE_PATH . DIRECTORY_SEPARATOR . 'avatars' . DIRECTORY_SEPARATOR . basename((string)$currentUser['avatar']);
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            } else {
                storeOldInput();
                flash('error', $result['error']);
                $this->redirect('/profile/edit');
            }
        }

        $userModel->update(userId(), $data);
        clearOldInput();

        (new AuthService())->refreshSession();

        flash('success', 'Hồ sơ đã được cập nhật thành công.');
        $this->redirect('/profile');
    }

    public function changePassword(array $params = []): void
    {
        $this->requireLogin();
        $this->csrfValidate();

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['new_password_confirmation'] ?? '';

        $userModel = new User();
        $user = $userModel->findById(userId());

        if (!password_verify($current, $user['password_hash'])) {
            flash('error', 'Mật khẩu hiện tại không đúng.');
            $this->redirect('/profile/edit');
        }

        $errors = $this->validate(
            ['password' => $new, 'password_confirmation' => $confirm],
            ['password' => 'required|password_rules|confirmed']
        );
        if (!empty($errors)) {
            flash('errors', serialize($errors));
            $this->redirect('/profile/edit');
        }

        if (password_verify($new, $user['password_hash'])) {
            flash('error', 'Mật khẩu mới không được trùng với mật khẩu hiện tại.');
            $this->redirect('/profile/edit');
        }

        $userModel->update(userId(), [
            'password_hash'  => password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]),
            'remember_token' => null,
        ]);

        session_regenerate_id(true);
        flash('success', 'Mật khẩu đã được thay đổi thành công.');
        $this->redirect('/profile');
    }

    public function myDocuments(array $params = []): void
    {
        $this->requireLogin();
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $status   = trim((string)($_GET['status'] ?? ''));
        $allowed  = ['approved', 'pending', 'rejected', 'hidden'];
        if (!in_array($status, $allowed, true)) {
            $status = '';
        }

        $perPage  = Config::get('pagination.per_page', 12);
        $docModel = new Document();

        $this->view('profile.my_documents', [
            'title'        => 'Tài liệu của tôi — TMU Tài Liệu',
            'docs'         => $docModel->getUserAllDocuments(userId(), $page, $perPage, $status),
            'status'       => $status,
            'statusCounts' => $docModel->getUserStatusCounts(userId()),
        ]); 
    }

    public function favorites(array $params = []): void
    {
        $this->requireLogin();
        $page          = max(1, (int)($_GET['page'] ?? 1));
        $perPage       = Config::get('pagination.per_page', 12);
        $favoriteModel = new Favorite();

        $this->view('profile.favorites', [
            'title'     => 'Tài liệu yêu thích — TMU Tài Liệu',
            'favorites' => $favoriteModel->getUserFavorites(userId(), $page, $perPage),
        ]);
    }

    public function history(array $params = []): void
    {
        $this->requireLogin();
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $perPage   = Config::get('pagination.per_page', 12);
        $histModel = new DownloadHistory();

        $this->view('profile.history', [
            'title'   => 'Lịch sử tải xuống — TMU Tài Liệu',
            'history' => $histModel->getUserHistory(userId(), $page, $perPage),
        ]);
    }

    public function notifications(array $params = []): void
    {
        $this->requireLogin();
        $notifModel = new Notification();

        // Lấy danh sách trước khi đánh dấu đã đọc để giữ trạng thái tin mới cho lần xem này
        $notifications = $notifModel->getForUser(userId(), 50);
        $notifModel->markAllRead(userId());

        $this->view('profile.notifications', [
            'title'         => 'Thông báo — TMU Tài Liệu',
            'notifications' => $notifications,
        ]);
    }

    /**
     * Serve user avatar image safely from storage outside public root.
     */
    public function avatar(array $params = []): void
    {
        $user = (new User())->findById((int)($params['id'] ?? 0));
        if (!$user || empty($user['avatar'])) {
            http_response_code(404);
            exit;
        }

        $avatarDir = realpath(STORAGE_PATH . DIRECTORY_SEPARATOR . 'avatars');
        $filename  = basename((string)$user['avatar']);
        $filePath  = $avatarDir ? realpath($avatarDir . DIRECTORY_SEPARATOR . $filename) : false;

        if (
            !$avatarDir
            || !$filePath
            || !str_starts_with($filePath, $avatarDir . DIRECTORY_SEPARATOR)
            || !is_file($filePath)
        ) {
            http_response_code(404);
            exit;
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath) ?: '';
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowed, true)) {
            http_response_code(403);
            exit;
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        readfile($filePath);
        exit;
    }
}

