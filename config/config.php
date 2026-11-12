<?php
declare(strict_types=1);

$storagePath = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(__DIR__) . '/storage';

return [
    'app' => [
        'name'     => 'TMU Tài Liệu',
        'url'      => 'http://localhost/Web%20Php/public',
        'env'      => 'development',
        'debug'    => true,
        'timezone' => 'Asia/Ho_Chi_Minh',
        'locale'   => 'vi',
    ],
    'db' => [
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'tmu',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
        'options'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],
    'upload' => [
        'max_size'      => 52428800, // 50MB in bytes
        'allowed_extensions' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'rar'],
        'allowed_mimes' => [
            'application/pdf',
            'application/x-pdf',
            'application/acrobat',
            'applications/vnd.pdf',
            'text/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/CDFV2',
            'application/x-ole-storage',
            'text/plain',
            'text/csv',
            'inode/x-empty',
            'application/x-empty',
            'application/zip',
            'application/x-zip',
            'application/x-zip-compressed',
            'multipart/x-zip',
            'application/octet-stream',
            'application/x-rar',
            'application/x-rar-compressed',
            'application/vnd.rar',
        ],
        'document_path'  => $storagePath . '/documents/',
        'avatar_path'    => $storagePath . '/avatars/',
        'thumbnail_path' => $storagePath . '/thumbnails/',
    ],
    'session' => [
        'name'     => 'tmu_session',
        'lifetime' => 7200,
        'secure'   => false,
        'httponly' => true,
    ],
    'pagination' => [
        'per_page' => 12,
    ],
    'ai' => [
        // 'auto' tự nhận diện theo key (sk-or-... -> OpenRouter, AIza... -> Gemini), hoặc đặt 'openrouter' / 'gemini'
        'provider'         => 'auto',
        // Dán OpenRouter API Key hoặc Gemini API Key vào đây (hoặc đặt biến môi trường OPENROUTER_API_KEY / GEMINI_API_KEY)
        'api_key'          => getenv('OPENROUTER_API_KEY') ?: (getenv('GEMINI_API_KEY') ?: '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'),
        'openrouter_model' => 'nvidia/nemotron-3-ultra-550b-a55b:free',
        'gemini_model'     => 'gemini-2.5-flash',
        'reasoning'        => false,
        'timeout'          => 60,
        'max_pdf_bytes'    => 8388608, // 8MB tối đa gửi kèm PDF (cho Gemini)
    ],
];

