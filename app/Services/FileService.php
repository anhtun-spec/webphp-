<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

class FileService
{
    private array $config;

    public function __construct()
    {
        $this->config = Config::get('upload');
    }

    public function uploadDocument(array $file): array
    {
        // Basic checks
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => $this->uploadErrorMessage($file['error'] ?? -1)];
        }

        // Size check
        if ($file['size'] > $this->config['max_size']) {
            $maxMB = $this->config['max_size'] / 1048576;
            return ['success' => false, 'error' => "File quá lớn. Giới hạn tối đa {$maxMB}MB."];
        }

        // Extension check
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->config['allowed_extensions'])) {
            $allowed = implode(', ', $this->config['allowed_extensions']);
            return ['success' => false, 'error' => "Định dạng file không được hỗ trợ. Chấp nhận: {$allowed}."];
        }

        // MIME type check
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';

        if (!in_array($mimeType, $this->config['allowed_mimes'], true)) {
            return ['success' => false, 'error' => 'File không hợp lệ hoặc nội dung không khớp định dạng.'];
        }

        // Checksum
        $checksum = hash_file('sha256', $file['tmp_name']) ?: uniqid('', true);

        // Generate unique storage filename
        $storageName = $checksum . '.' . $ext;
        $destPath    = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->config['document_path'] . 'pending/' . $storageName);

        // Ensure directory exists
        $targetDir = dirname($destPath);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        if (!@move_uploaded_file($file['tmp_name'], $destPath)) {
            // Fallback to copy if move_uploaded_file fails in specific dev environments
            if (!@copy($file['tmp_name'], $destPath)) {
                return ['success' => false, 'error' => 'Không thể lưu file vào thư mục lưu trữ. Vui lòng kiểm tra quyền ghi thư mục storage/documents/pending.'];
            }
            @unlink($file['tmp_name']);
        }

        return [
            'success'           => true,
            'file_path'         => $storageName,
            'original_filename' => $file['name'],
            'mime_type'         => $mimeType,
            'file_size'         => $file['size'],
            'checksum'          => $checksum,
        ];
    }

    private function normalizePath(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    public function moveToApproved(string $filename): bool
    {
        $filename = basename($filename);
        if ($filename === '') {
            return false;
        }

        $src  = $this->normalizePath($this->config['document_path'] . 'pending/' . $filename);
        $dest = $this->normalizePath($this->config['document_path'] . 'approved/' . $filename);

        if (!is_dir(dirname($dest))) {
            @mkdir(dirname($dest), 0755, true);
        }

        // Nếu tệp đã nằm sẵn ở approved/ (ví dụ tài liệu từng được duyệt trước khi sửa lại)
        if (file_exists($dest)) {
            if (file_exists($src)) {
                @unlink($src);
            }
            return true;
        }

        if (!file_exists($src)) {
            return false;
        }

        if (@rename($src, $dest)) {
            return true;
        }

        // Dự phòng nếu rename() thất bại trên Windows: copy rồi xóa tệp nguồn
        if (@copy($src, $dest)) {
            @unlink($src);
            return true;
        }

        return false;
    }

    public function moveToPending(string $filename, bool $keepApprovedCopy = false): bool
    {
        $filename = basename($filename);
        if ($filename === '') {
            return false;
        }

        $src  = $this->normalizePath($this->config['document_path'] . 'approved/' . $filename);
        $dest = $this->normalizePath($this->config['document_path'] . 'pending/' . $filename);

        if (!is_dir(dirname($dest))) {
            @mkdir(dirname($dest), 0755, true);
        }

        if (file_exists($dest)) {
            if (!$keepApprovedCopy && file_exists($src)) {
                @unlink($src);
            }
            return true;
        }

        if (!file_exists($src)) {
            return false;
        }

        if ($keepApprovedCopy) {
            return @copy($src, $dest);
        }

        if (@rename($src, $dest)) {
            return true;
        }

        if (@copy($src, $dest)) {
            @unlink($src);
            return true;
        }

        return false;
    }

    public function deleteFile(string $filename, ?string $onlyDir = null): void
    {
        $dirs = $onlyDir !== null ? [$onlyDir] : ['pending', 'approved'];
        foreach ($dirs as $dir) {
            $path = $this->config['document_path'] . $dir . '/' . $filename;
            if (file_exists($path)) @unlink($path);
        }
    }

    public function uploadAvatar(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload ảnh thất bại.'];
        }
        if ($file['size'] > 2097152) { // 2MB
            return ['success' => false, 'error' => 'Ảnh đại diện không được vượt quá 2MB.'];
        }
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            return ['success' => false, 'error' => 'Chỉ chấp nhận ảnh JPG, PNG, GIF, WEBP.'];
        }
        $ext      = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'][$mimeType];
        $filename = uniqid('avatar_', true) . '.' . $ext;
        $destPath = $this->config['avatar_path'] . $filename;
        if (!is_dir(dirname($destPath))) {
            @mkdir(dirname($destPath), 0755, true);
        }
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'error' => 'Không thể lưu ảnh.'];
        }
        return ['success' => true, 'filename' => $filename];
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File vượt quá giới hạn kích thước.',
            UPLOAD_ERR_PARTIAL => 'File chỉ được tải lên một phần.',
            UPLOAD_ERR_NO_FILE => 'Chưa chọn file.',
            default => 'Lỗi upload file. Vui lòng thử lại.',
        };
    }
}

