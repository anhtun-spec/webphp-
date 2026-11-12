<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Comment extends Model
{
    protected static string $table = 'comments';

    public function getByDocument(int $documentId): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, u.username, u.full_name, u.avatar
             FROM comments c
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.document_id = ? AND c.parent_id IS NULL AND c.is_hidden = 0
             ORDER BY c.created_at ASC",
            [$documentId]
        );
    }

    public function getReplies(int $parentId): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, u.username, u.full_name, u.avatar
             FROM comments c
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.parent_id = ? AND c.is_hidden = 0
             ORDER BY c.created_at ASC",
            [$parentId]
        );
    }

    /**
     * Resolve the top-level (root) comment ID to enforce a maximum of 2-level comment depth.
     */
    public function getRootParentId(int $commentId): int
    {
        $current = $this->findById($commentId);
        if (!$current) {
            return $commentId;
        }

        while (!empty($current['parent_id'])) {
            $parent = $this->findById((int)$current['parent_id']);
            if (!$parent) {
                break;
            }
            $current = $parent;
        }

        return (int)$current['id'];
    }

    public function getAllForAdmin(int $page, int $perPage): array
    {
        $total = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM comments");
        $offset = ($page - 1) * $perPage;
        $items = $this->db->fetchAll(
            "SELECT c.*, u.username, u.full_name, d.title AS document_title, d.id AS document_id
             FROM comments c
             LEFT JOIN users u ON c.user_id = u.id
             LEFT JOIN documents d ON c.document_id = d.id
             ORDER BY c.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
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

