<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Subject extends Model
{
    protected static string $table = 'subjects';

    public function getByFaculty(int $facultyId): array
    {
        return $this->db->fetchAll(
            "SELECT s.*,
                    COUNT(d.id) AS document_count
             FROM subjects s
             LEFT JOIN documents d ON d.subject_id = s.id AND d.status = 'approved' AND d.deleted_at IS NULL
             WHERE s.faculty_id = ? AND s.is_active = 1
             GROUP BY s.id
             ORDER BY s.sort_order ASC, s.name ASC",
            [$facultyId]
        );
    }

    public function getBySlug(int $facultyId, string $slug): ?array
    {
        return $this->db->fetch(
            "SELECT s.*, f.name AS faculty_name, f.slug AS faculty_slug
             FROM subjects s
             LEFT JOIN faculties f ON s.faculty_id = f.id
             WHERE s.faculty_id = ? AND s.slug = ? AND s.is_active = 1",
            [$facultyId, $slug]
        );
    }

    public function getWithFaculty(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT s.*, f.name AS faculty_name, f.slug AS faculty_slug, f.id AS faculty_id,
                    f.is_active AS faculty_is_active,
                    u.name AS university_name
             FROM subjects s
             LEFT JOIN faculties f ON s.faculty_id = f.id
             LEFT JOIN universities u ON f.university_id = u.id
             WHERE s.id = ?",
            [$id]
        );
    }

    public function getActive(): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, f.name AS faculty_name
             FROM subjects s
             INNER JOIN faculties f ON s.faculty_id = f.id
             WHERE s.is_active = 1 AND f.is_active = 1
             ORDER BY f.sort_order ASC, s.sort_order ASC, s.name ASC"
        );
    }

    public function getAllForAdmin(): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, f.name AS faculty_name,
                    COUNT(d.id) AS document_count
             FROM subjects s
             LEFT JOIN faculties f ON s.faculty_id = f.id
             LEFT JOIN documents d ON d.subject_id = s.id AND d.deleted_at IS NULL
             GROUP BY s.id
             ORDER BY f.sort_order ASC, s.sort_order ASC, s.name ASC"
        );
    }
}
