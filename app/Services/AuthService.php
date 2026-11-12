<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\User;

class AuthService
{
    private User $userModel;
    private Database $db;

    public function __construct()
    {
        $this->userModel = new User();
        $this->db        = Database::getInstance();
    }

    public function register(array $data): array
    {
        if ($this->userModel->emailExists($data['email'])) {
            return ['success' => false, 'errors' => ['email' => ['Email này đã được sử dụng.']]];
        }
        if ($this->userModel->usernameExists($data['username'])) {
            return ['success' => false, 'errors' => ['username' => ['Tên đăng nhập này đã được sử dụng.']]];
        }
        if (!empty($data['student_id']) && $this->userModel->studentIdExists($data['student_id'])) {
            return ['success' => false, 'errors' => ['student_id' => ['Mã sinh viên này đã được đăng ký.']]];
        }

        $userId = $this->userModel->create([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'full_name'     => $data['full_name'],
            'student_id'    => $data['student_id'] ?? null,
            'role'          => 'member',
            'status'        => 'active',
        ]);

        $user = $this->userModel->findById($userId);
        $this->startSession($user);

        return ['success' => true, 'user' => $user];
    }

    public function login(string $email, string $password, bool $remember = false): array
    {
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $lockInfo = $this->checkRateLimit($email, $ip);

        if ($lockInfo['locked']) {
            return ['success' => false, 'error' => "Quá nhiều lần thử. Vui lòng đợi {$lockInfo['wait_minutes']} phút."];
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $attempts = $this->recordFailedAttempt($email, $ip);
            if ($attempts >= 5) {
                return ['success' => false, 'error' => 'Quá nhiều lần thử. Tài khoản tạm khóa 15 phút.'];
            }
            return ['success' => false, 'error' => 'Email hoặc mật khẩu không đúng.'];
        }

        if ($user['status'] === 'locked') {
            return ['success' => false, 'error' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.'];
        }

        $this->clearFailedAttempts($email, $ip);
        $this->userModel->updateLastLogin((int)$user['id']);
        $this->startSession($user, $remember);

        return ['success' => true, 'user' => $user];
    }

    public function logout(): void
    {
        if (auth()) {
            $this->clearRememberToken((int)userId());
        }
        $this->deleteRememberCookie();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    public function sendPasswordReset(string $email): ?string
    {
        unset($_SESSION['dev_reset_token'], $_SESSION['dev_reset_email']);

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            return null;
        }

        $rawToken    = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $rawToken);
        $this->userModel->setResetToken((int)$user['id'], $hashedToken);

        return $rawToken;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->userModel->findByResetToken($token);
        if (!$user) {
            return false;
        }

        $this->userModel->update((int)$user['id'], [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]),
        ]);
        $this->userModel->clearResetToken((int)$user['id']);
        $this->clearRememberToken((int)$user['id']);

        return true;
    }

    public function checkRememberMe(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE || auth() || empty($_COOKIE['tmu_remember'])) {
            return;
        }

        $parts = explode(':', (string)$_COOKIE['tmu_remember'], 2);
        if (count($parts) !== 2) {
            $this->deleteRememberCookie();
            return;
        }

        [$rawUserId, $rawToken] = $parts;
        $userId = (int)$rawUserId;
        if ($userId <= 0 || $rawToken === '') {
            $this->deleteRememberCookie();
            return;
        }

        $user = $this->userModel->findById($userId);
        if (
            !$user
            || $user['status'] === 'locked'
            || empty($user['remember_token'])
            || !hash_equals((string)$user['remember_token'], hash('sha256', $rawToken))
        ) {
            $this->deleteRememberCookie();
            return;
        }

        $this->startSession($user, true);
    }

    private function startSession(array $user, bool $remember = false): void
    {
        session_regenerate_id(true);
        if ($remember && !headers_sent()) {
            $rawToken    = bin2hex(random_bytes(32));
            $hashedToken = hash('sha256', $rawToken);
            try {
                $this->userModel->update((int)$user['id'], ['remember_token' => $hashedToken]);
                $params = session_get_cookie_params();
                setcookie('tmu_remember', $user['id'] . ':' . $rawToken, [
                    'expires'  => time() + 2592000, // 30 ngày
                    'path'     => $params['path'] ?: '/',
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => true,
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]);
            } catch (\Throwable $e) {
                error_log('Remember token update failed: ' . $e->getMessage());
            }
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user']    = [
            'id'        => $user['id'],
            'username'  => $user['username'],
            'email'     => $user['email'],
            'full_name' => $user['full_name'],
            'avatar'    => $user['avatar'],
            'role'      => $user['role'],
            'status'    => $user['status'],
        ];
    }

    private function clearRememberToken(int $userId): void
    {
        try {
            $this->userModel->update($userId, ['remember_token' => null]);
        } catch (\Throwable $e) {
        }
    }

    private function deleteRememberCookie(): void
    {
        if (!headers_sent() && isset($_COOKIE['tmu_remember'])) {
            $params = session_get_cookie_params();
            setcookie('tmu_remember', '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'] ?: '/',
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => true,
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
            unset($_COOKIE['tmu_remember']);
        }
    }

    private function checkRateLimit(string $email, string $ip): array
    {
        try {
            $row = $this->db->fetch(
                "SELECT COUNT(*) AS cnt, MAX(attempted_at) AS last_attempt
                 FROM login_attempts
                 WHERE (email = ? OR ip_address = ?)
                   AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
                [$email, $ip]
            );
            $count = (int)($row['cnt'] ?? 0);
            if ($count >= 5 && !empty($row['last_attempt'])) {
                $unlockAt = strtotime((string)$row['last_attempt']) + 900;
                $wait     = max(1, (int)ceil(($unlockAt - time()) / 60));
                return ['locked' => true, 'wait_minutes' => $wait];
            }
        } catch (\Throwable $e) {
            $lockUntil = $_SESSION['login_lock'][$email] ?? 0;
            if ($lockUntil > time()) {
                return ['locked' => true, 'wait_minutes' => (int)ceil(($lockUntil - time()) / 60)];
            }
        }

        return ['locked' => false, 'wait_minutes' => 0];
    }

    private function recordFailedAttempt(string $email, string $ip): int
    {
        try {
            $this->db->query(
                "INSERT INTO login_attempts (email, ip_address, attempted_at) VALUES (?, ?, NOW())",
                [$email, $ip]
            );
            return (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM login_attempts
                 WHERE (email = ? OR ip_address = ?)
                   AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
                [$email, $ip]
            );
        } catch (\Throwable $e) {
            $attempts = ($_SESSION['login_attempts'][$email] ?? 0) + 1;
            $_SESSION['login_attempts'][$email] = $attempts;
            if ($attempts >= 5) {
                $_SESSION['login_lock'][$email] = time() + 900;
            }
            return $attempts;
        }
    }

    private function clearFailedAttempts(string $email, string $ip): void
    {
        unset($_SESSION['login_attempts'][$email], $_SESSION['login_lock'][$email]);
        try {
            $this->db->query(
                "DELETE FROM login_attempts WHERE email = ? OR ip_address = ? OR attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)",
                [$email, $ip]
            );
        } catch (\Throwable $e) {
        }
    }

    public function refreshSession(): void
    {
        if (!auth()) {
            return;
        }
        $user = $this->userModel->findById((int)userId());
        if ($user) {
            $this->startSession($user);
        }
    }
}