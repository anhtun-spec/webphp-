<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class University extends Model
{
    protected static string $table = 'universities';

    public function getActive(): array
    {
        return $this->findAll(['is_active' => 1], 'sort_order ASC, name ASC');
    }
}

