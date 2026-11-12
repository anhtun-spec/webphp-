<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class DocumentType extends Model
{
    protected static string $table = 'document_types';

    public function getActive(): array
    {
        return $this->findAll(['is_active' => 1], 'sort_order ASC, name ASC');
    }

    public function getAllForAdmin(): array
    {
        return $this->db->fetchAll(
            "SELECT dt.*, COUNT(d.id) AS document_count
             FROM document_types dt
             LEFT JOIN documents d ON d.document_type_id = dt.id AND d.deleted_at IS NULL
             GROUP BY dt.id
             ORDER BY dt.sort_order ASC, dt.name ASC"
        );
    }
}

