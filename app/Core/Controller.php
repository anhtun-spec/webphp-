<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = [], string $layout = 'main'): void
    {
        // Make data available as variables
        extract($data);

        // Capture view content
        ob_start();
        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $template) . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }
        require $viewFile;
        $content = ob_get_clean();

        // Render layout
        $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . url($url));
        exit;
    }

    protected function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $ref);
        exit;
    }

    protected function requireLogin(): void
    {
        if (!auth()) {
            flash('error', 'Vui lòng đăng nhập để tiếp tục.');
            $this->redirect('/login');
        }

        $user = (new \App\Models\User())->findById((int)userId());
        if (!$user || $user['status'] === 'locked') {
            (new \App\Services\AuthService())->logout();
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            flash('error', 'Tài khoản của bạn đã bị khóa hoặc không tồn tại.');
            $this->redirect('/login');
        }

        // Sync role and status changes immediately
        if (isset($_SESSION['user'])) {
            $_SESSION['user']['role']   = $user['role'];
            $_SESSION['user']['status'] = $user['status'];
        }
    }

    protected function requireRole(string ...$roles): void
    {
        $this->requireLogin();
        $user = currentUser();
        if (!in_array($user['role'] ?? '', $roles)) {
            http_response_code(403);
            $this->view('errors.403', ['user' => $user], 'main');
            exit;
        }
    }

    protected function validate(array $data, array $rules): array
    {
        return (new Validator($data, $rules))->validate();
    }

    protected function csrfValidate(): void
    {
        if (
            ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
            && empty($_POST)
            && empty($_FILES)
            && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
        ) {
            $maxPost = ini_get('post_max_size') ?: '40M';
            flash('error', "Dung lượng tệp hoặc dữ liệu gửi lên vượt quá giới hạn của máy chủ ({$maxPost}).");
            $this->back();
        }

        $token = $_POST['_csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!verifyCsrf($token)) {
            if (isAjax()) {
                $this->json(['error' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.'], 403);
            }
            http_response_code(403);
            die('CSRF token mismatch. Vui lòng thử lại.');
        }
    }
}

