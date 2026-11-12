<?php
declare(strict_types=1);

/**
 * Escape HTML special chars.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Generate application URL.
 */
function url(string $path = ''): string
{
    if (isset($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $base = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim($scriptDir, '/');
    } else {
        $base = rtrim(\App\Core\Config::get('app.url', 'http://localhost/Web%20Php/public'), '/');
    }
    return $base . '/' . ltrim($path, '/');
}

/**
 * Generate asset URL.
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Generate avatar URL for a user when the avatar file exists in storage.
 */
function avatarUrl(int $userId, ?string $avatar): ?string
{
    if (empty($avatar) || $userId <= 0) {
        return null;
    }

    $storagePath = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(__DIR__, 2) . '/storage';
    $filename = basename($avatar);
    $filePath = $storagePath . DIRECTORY_SEPARATOR . 'avatars' . DIRECTORY_SEPARATOR . $filename;
    if (!is_file($filePath)) {
        return null;
    }

    $version = substr(md5($filename), 0, 8);
    return url('/users/' . $userId . '/avatar?v=' . $version);
}

/**
 * Check if user is authenticated.
 */
function auth(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Get current authenticated user data.
 */
function currentUser(): ?array
{
    if (!auth()) return null;
    return $_SESSION['user'] ?? null;
}

/**
 * Get current user ID.
 */
function userId(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Check if current user has a role.
 */
function hasRole(string $role): bool
{
    $user = currentUser();
    return $user && ($user['role'] ?? '') === $role;
}

/**
 * Check if current user is admin.
 */
function isAdmin(): bool
{
    return hasRole('admin');
}

/**
 * Check if current user is moderator or admin.
 */
function isModerator(): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, ['admin', 'moderator']);
}

/**
 * Set flash message.
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][$type][] = $message;
}

/**
 * Get and clear flash messages.
 */
function getFlash(string $type): array
{
    $messages = $_SESSION['flash'][$type] ?? [];
    unset($_SESSION['flash'][$type]);
    return $messages;
}

/**
 * Generate CSRF token.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token.
 */
function verifyCsrf(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * CSRF hidden input field.
 */
function csrfField(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(csrfToken()) . '">';
}

/**
 * Format file size.
 */
function formatSize(int $bytes): string
{
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)       return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

/**
 * Format date to Vietnamese style.
 */
function formatDate(string $date, string $format = 'd/m/Y'): string
{
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Truncate text.
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Generate slug from Vietnamese text.
 */
function slug(string $text): string
{
    $vi = ['à','á','ả','ã','ạ','ă','ắ','ằ','ẳ','ẵ','ặ','â','ấ','ầ','ẩ','ẫ','ậ',
           'è','é','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ','ì','í','ỉ','ĩ','ị',
           'ò','ó','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ',
           'ù','ú','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự','ý','ỳ','ỷ','ỹ','ỵ',
           'đ','À','Á','Ả','Ã','Ạ','Ă','Ắ','Ằ','Ẳ','Ẵ','Ặ','Â','Ấ','Ầ','Ẩ','Ẫ','Ậ',
           'È','É','Ẻ','Ẽ','Ẹ','Ê','Ế','Ề','Ể','Ễ','Ệ','Ì','Í','Ỉ','Ĩ','Ị',
           'Ò','Ó','Ỏ','Õ','Ọ','Ô','Ố','Ồ','Ổ','Ỗ','Ộ','Ơ','Ớ','Ờ','Ở','Ỡ','Ợ',
           'Ù','Ú','Ủ','Ũ','Ụ','Ư','Ứ','Ừ','Ử','Ữ','Ự','Ý','Ỳ','Ỷ','Ỹ','Ỵ','Đ'];
    $en = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
           'e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i',
           'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
           'u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y',
           'd','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
           'e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i',
           'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
           'u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','d'];
    $text = str_replace($vi, $en, $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Redirect helper (outside of controller context).
 */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Check if request is AJAX.
 */
function isAjax(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Old input value (for form repopulation after validation error).
 */
function old(string $key, mixed $default = ''): string
{
    return e($_SESSION['old_input'][$key] ?? $default);
}

/**
 * Return Vietnamese label and Bootstrap badge classes for document status.
 *
 * @return array{label: string, badge: string, bs: string}
 */
function documentStatusInfo(string $status): array
{
    return match ($status) {
        'approved' => ['label' => 'Đã duyệt',   'badge' => 'badge-approved', 'bs' => 'bg-success'],
        'pending'  => ['label' => 'Chờ duyệt',  'badge' => 'badge-pending',  'bs' => 'bg-warning text-dark'],
        'rejected' => ['label' => 'Bị từ chối', 'badge' => 'badge-rejected', 'bs' => 'bg-danger'],
        'hidden'   => ['label' => 'Đã ẩn',      'badge' => 'badge-hidden',   'bs' => 'bg-secondary'],
        'draft'    => ['label' => 'Bản nháp',   'badge' => 'badge-draft',    'bs' => 'bg-info text-dark'],
        default    => ['label' => $status,      'badge' => 'bg-secondary',   'bs' => 'bg-secondary'],
    };
}

/**
 * Store old input, excluding passwords and CSRF tokens.
 */
function storeOldInput(): void
{
    $excluded = [
        '_csrf_token',
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'new_password_confirmation',
    ];
    $_SESSION['old_input'] = array_diff_key($_POST, array_flip($excluded));
}

/**
 * Clear old input.
 */
function clearOldInput(): void
{
    unset($_SESSION['old_input']);
}

