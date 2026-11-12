<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Favorite extends Model
{
    protected static string $table = 'favorites';

    public function isFavorited(int $userId, int $documentId): bool
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM favorites WHERE user_id = ? AND document_id = ?",
            [$userId, $documentId]
        ) > 0;
    }

    public function toggle(int $userId, int $documentId): bool
    {
        if ($this->isFavorited($userId, $documentId)) {
            $this->db->query(
                "DELETE FROM favorites WHERE user_id = ? AND document_id = ?",
                [$userId, $documentId]
            );
            return false; // removed
        } else {
            $this->create(['user_id' => $userId, 'document_id' => $documentId]);
            return true; // added
        }
    }

    public function getUserFavorites(int $userId, int $page, int $perPage): array
    {
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM favorites f
             JOIN documents d ON f.document_id = d.id
             WHERE f.user_id = ? AND d.deleted_at IS NULL AND d.status = 'approved'",
            [$userId]
        );
        $offset = ($page - 1) * $perPage;
        $items = $this->db->fetchAll(
            "SELECT d.id, d.title, d.slug, d.view_count, d.download_count,
                    d.created_at, d.mime_type, d.file_size, f.created_at AS favorited_at,
                    s.name AS subject_name, dt.name AS type_name, fac.name AS faculty_name
             FROM favorites f
             JOIN documents d ON f.document_id = d.id
             LEFT JOIN subjects s ON d.subject_id = s.id
             LEFT JOIN faculties fac ON s.faculty_id = fac.id
             LEFT JOIN document_types dt ON d.document_type_id = dt.id
             WHERE f.user_id = ? AND d.deleted_at IS NULL AND d.status = 'approved'
             ORDER BY f.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            [$userId]
        );
        return [
            'items'        => $items,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }
}

