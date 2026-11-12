<?php
declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?",
            [$id]
        );
    }

    /**
     * Build SQL WHERE clause and parameter bindings from conditions.
     *
     * @return array{0: string, 1: array}
     */
    protected function buildWhereClause(array $conditions): array
    {
        if (empty($conditions)) {
            return ['', []];
        }

        $where = [];
        $params = [];
        foreach ($conditions as $key => $value) {
            if ($value === null) {
                $where[] = "{$key} IS NULL";
            } else {
                $where[] = "{$key} = ?";
                $params[] = $value;
            }
        }

        return [' WHERE ' . implode(' AND ', $where), $params];
    }

    public function findAll(
        array $conditions = [],
        string $orderBy = 'id DESC',
        int $limit = 0,
        int $offset = 0
    ): array {
        [$whereSql, $params] = $this->buildWhereClause($conditions);
        $sql = "SELECT * FROM " . static::$table . $whereSql;

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        if ($offset > 0) {
            $sql .= " OFFSET {$offset}";
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        return $this->db->insert(static::$table, $data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->db->update(static::$table, $data, [static::$primaryKey => $id]) > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->query(
            "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?",
            [$id]
        );
        return $stmt->rowCount() > 0;
    }

    public function softDelete(int $id): bool
    {
        return $this->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
    }

    public function count(array $conditions = []): int
    {
        [$whereSql, $params] = $this->buildWhereClause($conditions);
        $sql = "SELECT COUNT(*) FROM " . static::$table . $whereSql;
        return (int)$this->db->fetchColumn($sql, $params);
    }

    public function paginate(int $page, int $perPage, array $conditions = [], string $orderBy = 'id DESC'): array
    {
        $total = $this->count($conditions);
        $offset = ($page - 1) * $perPage;
        $items = $this->findAll($conditions, $orderBy, $perPage, $offset);

        return [
            'items'        => $items,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }
}

