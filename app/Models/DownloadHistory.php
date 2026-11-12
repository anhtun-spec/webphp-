<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class DownloadHistory extends Model
{
    protected static string $table = 'download_history';

    public function record(int $documentId, ?int $userId): void
    {
        $this->create([
            'document_id' => $documentId,
            'user_id'     => $userId,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent'  => isset($_SERVER['HTTP_USER_AGENT'])
                ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500)
                : null,
        ]);
    }

    public function getUserHistory(int $userId, int $page, int $perPage): array
    {
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(DISTINCT dh.document_id)
             FROM download_history dh
             JOIN documents d ON dh.document_id = d.id
             WHERE dh.user_id = ? AND d.deleted_at IS NULL",
            [$userId]
        );

        $offset = max(0, ($page - 1) * $perPage);
        $limit  = max(1, $perPage);

        $items = $this->db->fetchAll(
            "SELECT d.id AS document_id, d.title, d.slug,
                    MAX(dh.downloaded_at) AS downloaded_at,
                    COUNT(dh.id) AS user_download_count,
                    s.name AS subject_name, dt.name AS type_name
             FROM download_history dh
             JOIN documents d ON dh.document_id = d.id
             LEFT JOIN subjects s ON d.subject_id = s.id
             LEFT JOIN document_types dt ON d.document_type_id = dt.id
             WHERE dh.user_id = ? AND d.deleted_at IS NULL
             GROUP BY d.id, d.title, d.slug, s.name, dt.name
             ORDER BY downloaded_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            [$userId]
        );

        return [
            'items'        => $items,
            'total'        => $total,
            'per_page'     => $limit,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $limit),
        ];
    }

    public function getTotalDownloads(): int
    {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM download_history");
    }
}