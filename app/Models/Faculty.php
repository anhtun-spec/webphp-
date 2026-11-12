<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Faculty extends Model
{
    protected static string $table = 'faculties';

    public function getAllWithSubjectCount(): array
    {
        return $this->db->fetchAll(
            "SELECT f.*,
                    u.name AS university_name,
                    COUNT(s.id) AS subject_count,
                    COUNT(DISTINCT d.id) AS document_count
             FROM faculties f
             LEFT JOIN universities u ON f.university_id = u.id
             LEFT JOIN subjects s ON s.faculty_id = f.id AND s.is_active = 1
             LEFT JOIN documents d ON d.subject_id = s.id AND d.status = 'approved' AND d.deleted_at IS NULL
             WHERE f.is_active = 1
             GROUP BY f.id
             ORDER BY f.sort_order ASC, f.name ASC"
        );
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->db->fetch(
            "SELECT f.*, u.name AS university_name
             FROM faculties f
             LEFT JOIN universities u ON f.university_id = u.id
             WHERE f.slug = ? AND f.is_active = 1",
            [$slug]
        );
    }

    public function getActive(): array
    {
        return $this->findAll(['is_active' => 1], 'sort_order ASC, name ASC');
    }

    public function getAllForAdmin(): array
    {
        return $this->db->fetchAll(
            "SELECT f.*, u.name AS university_name,
                    COUNT(s.id) AS subject_count
             FROM faculties f
             LEFT JOIN universities u ON f.university_id = u.id
             LEFT JOIN subjects s ON s.faculty_id = f.id
             GROUP BY f.id
             ORDER BY f.sort_order ASC, f.name ASC"
        );
    }

    public function slugExists(string $slug, int $universityId, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM faculties WHERE slug = ? AND university_id = ?";
        $params = [$slug, $universityId];
        if ($excludeId > 0) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        return (int)$this->db->fetchColumn($sql, $params) > 0;
    }
}
