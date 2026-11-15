<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Document extends Model
{
    protected static string $table = 'documents';

    /**
     * Get documents with joined info (user, subject, faculty, type).
     */
    public function getWithDetails(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT d.*,
                    u.username, u.full_name AS uploader_name, u.avatar AS uploader_avatar,
                    s.name AS subject_name, s.slug AS subject_slug,
                    f.name AS faculty_name, f.id AS faculty_id,
                    dt.name AS type_name, dt.slug AS type_slug,
                    approver.full_name AS approver_name
             FROM documents d
             LEFT JOIN users u        ON d.user_id = u.id
             LEFT JOIN subjects s     ON d.subject_id = s.id
             LEFT JOIN faculties f    ON s.faculty_id = f.id
             LEFT JOIN document_types dt ON d.document_type_id = dt.id
             LEFT JOIN users approver ON d.approved_by = approver.id
             WHERE d.id = ? AND d.deleted_at IS NULL",
            [$id]
        );
    }

    /**
     * Search/list documents with filters and pagination.
     */
    public function search(array $filters, int $page, int $perPage): array
    {
        $where = ["d.deleted_at IS NULL"];
        $params = [];

        // Status filter (default: approved for public; 'all' shows all statuses)
        $status = $filters['status'] ?? 'approved';
        if ($status !== 'all') {
            $where[] = "d.status = ?";
            $params[] = $status;
        }

        if (!empty($filters['query'])) {
            $query = trim((string)$filters['query']);
            $escapedLike = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query) . '%';
            $booleanQuery = $this->buildBooleanSearchQuery($query);

            if ($booleanQuery !== '') {
                $where[] = "(MATCH(d.title, d.description) AGAINST(? IN BOOLEAN MODE) OR d.title LIKE ? OR d.description LIKE ?)";
                $params[] = $booleanQuery;
                $params[] = $escapedLike;
                $params[] = $escapedLike;
            } else {
                $where[] = "(d.title LIKE ? OR d.description LIKE ?)";
                $params[] = $escapedLike;
                $params[] = $escapedLike;
            }
        }
        if (!empty($filters['faculty_id'])) {
            $where[] = "f.id = ?";
            $params[] = (int)$filters['faculty_id'];
        }
        if (!empty($filters['subject_id'])) {
            $where[] = "d.subject_id = ?";
            $params[] = (int)$filters['subject_id'];
        }
        if (!empty($filters['type_id'])) {
            $where[] = "d.document_type_id = ?";
            $params[] = (int)$filters['type_id'];
        }
        if (!empty($filters['user_id'])) {
            $where[] = "d.user_id = ?";
            $params[] = (int)$filters['user_id'];
        }

        // Sort
        $sortOptions = [
            'newest'    => 'd.created_at DESC',
            'popular'   => 'd.view_count DESC',
            'downloads' => 'd.download_count DESC',
        ];
        $orderBy = $sortOptions[$filters['sort'] ?? 'newest'] ?? 'd.created_at DESC';

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $countSql = "SELECT COUNT(*)
                     FROM documents d
                     LEFT JOIN subjects s ON d.subject_id = s.id
                     LEFT JOIN faculties f ON s.faculty_id = f.id
                     {$whereClause}";
        $total = (int)$this->db->fetchColumn($countSql, $params);

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT d.id, d.title, d.slug, d.description, d.file_size, d.mime_type,
                       d.view_count, d.download_count,
                       d.created_at, d.status,
                       u.username, u.full_name AS uploader_name,
                       s.name AS subject_name,
                       f.name AS faculty_name, f.id AS faculty_id,
                       dt.name AS type_name
                FROM documents d
                LEFT JOIN users u        ON d.user_id = u.id
                LEFT JOIN subjects s     ON d.subject_id = s.id
                LEFT JOIN faculties f    ON s.faculty_id = f.id
                LEFT JOIN document_types dt ON d.document_type_id = dt.id
                {$whereClause}
                ORDER BY {$orderBy}
                LIMIT {$perPage} OFFSET {$offset}";

        return [
            'items'        => $this->db->fetchAll($sql, $params),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }

    public function getPending(int $page, int $perPage): array
    {
        return $this->search(['status' => 'pending'], $page, $perPage);
    }

    public function getRecent(int $limit = 8): array
    {
        return $this->db->fetchAll(
            "SELECT d.id, d.title, d.slug, d.view_count, d.download_count,
                    d.created_at, d.mime_type, d.file_size,
                    u.full_name AS uploader_name,
                    s.name AS subject_name,
                    f.name AS faculty_name,
                    dt.name AS type_name
             FROM documents d
             LEFT JOIN users u        ON d.user_id = u.id
             LEFT JOIN subjects s     ON d.subject_id = s.id
             LEFT JOIN faculties f    ON s.faculty_id = f.id
             LEFT JOIN document_types dt ON d.document_type_id = dt.id
             WHERE d.status = 'approved' AND d.deleted_at IS NULL
             ORDER BY d.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function getPopular(int $limit = 8): array
    {
        return $this->db->fetchAll(
            "SELECT d.id, d.title, d.slug, d.view_count, d.download_count,
                    d.created_at, d.mime_type, d.file_size,
                    u.full_name AS uploader_name,
                    s.name AS subject_name,
                    f.name AS faculty_name,
                    dt.name AS type_name
             FROM documents d
             LEFT JOIN users u        ON d.user_id = u.id
             LEFT JOIN subjects s     ON d.subject_id = s.id
             LEFT JOIN faculties f    ON s.faculty_id = f.id
             LEFT JOIN document_types dt ON d.document_type_id = dt.id
             WHERE d.status = 'approved' AND d.deleted_at IS NULL
             ORDER BY d.download_count DESC, d.view_count DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function incrementView(int $id): void
    {
        $this->db->query("UPDATE documents SET view_count = view_count + 1 WHERE id = ?", [$id]);
    }

    public function incrementDownload(int $id): void
    {
        $this->db->query("UPDATE documents SET download_count = download_count + 1 WHERE id = ?", [$id]);
    }

    /**
     * Sanitize user input for MySQL FULLTEXT BOOLEAN MODE to prevent syntax errors.
     */
    private function buildBooleanSearchQuery(string $query): string
    {
        $sanitized = preg_replace('/[+\-><()~*@"\'`:\/\\\\^]+/u', ' ', $query) ?? '';
        $words = preg_split('/\s+/u', trim($sanitized), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = [];
        foreach ($words as $word) {
            $cleaned = trim($word, ".,:;!?/'\"-");
            if (mb_strlen($cleaned) >= 2) {
                $tokens[] = $cleaned . '*';
            }
        }
        return implode(' ', $tokens);
    }

    public function isFilePathInUse(string $filePath, int $excludeId = 0, ?string $status = null): bool
    {
        $sql = "SELECT COUNT(*) FROM documents WHERE file_path = ? AND deleted_at IS NULL";
        $params = [$filePath];
        if ($excludeId > 0) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        if ($status !== null) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        return (int)$this->db->fetchColumn($sql, $params) > 0;
    }

    public function checksumExists(string $checksum, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM documents WHERE checksum = ? AND deleted_at IS NULL";
        $params = [$checksum];
        if ($excludeId > 0) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        return (int)$this->db->fetchColumn($sql, $params) > 0;
    }

    public function getStats(): array
    {
        return [
            'total'    => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM documents WHERE deleted_at IS NULL"),
            'approved' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM documents WHERE status = 'approved' AND deleted_at IS NULL"),
            'pending'  => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM documents WHERE status = 'pending' AND deleted_at IS NULL"),
            'rejected' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM documents WHERE status = 'rejected' AND deleted_at IS NULL"),
            'downloads'=> (int)$this->db->fetchColumn("SELECT SUM(download_count) FROM documents WHERE deleted_at IS NULL"),
        ];
    }

    public function getUserDocuments(int $userId, int $page, int $perPage): array
    {
        return $this->search(['user_id' => $userId, 'status' => 'approved'], $page, $perPage);
    }

    public function getUserAllDocuments(int $userId, int $page, int $perPage, string $status = ''): array
    {
        $where  = "d.user_id = ? AND d.deleted_at IS NULL";
        $params = [$userId];

        if ($status !== '') {
            $where   .= " AND d.status = ?";
            $params[] = $status;
        }

        $offset = max(0, ($page - 1) * $perPage);
        $limit  = max(1, $perPage);

        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM documents d WHERE {$where}",
            $params
        );

        $items = $this->db->fetchAll(
            "SELECT d.id, d.title, d.slug, d.status, d.rejection_reason, d.view_count, d.download_count,
                    d.created_at, d.mime_type, d.file_size,
                    s.name AS subject_name, dt.name AS type_name, f.name AS faculty_name
             FROM documents d
             LEFT JOIN subjects s ON d.subject_id = s.id
             LEFT JOIN faculties f ON s.faculty_id = f.id
             LEFT JOIN document_types dt ON d.document_type_id = dt.id
             WHERE {$where}
             ORDER BY d.created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return [
            'items'        => $items,
            'total'        => $total,
            'per_page'     => $limit,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $limit),
        ];
    }

    public function getUserStatusCounts(int $userId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT status, COUNT(*) AS cnt
             FROM documents
             WHERE user_id = ? AND deleted_at IS NULL
             GROUP BY status",
            [$userId]
        );

        $counts = [
            'all'      => 0,
            'approved' => 0,
            'pending'  => 0,
            'rejected' => 0,
            'hidden'   => 0,
        ];

        foreach ($rows as $row) {
            $st  = (string)$row['status'];
            $cnt = (int)$row['cnt'];
            $counts['all'] += $cnt;
            if (array_key_exists($st, $counts)) {
                $counts[$st] = $cnt;
            }
        }

        return $counts;
    }
}
