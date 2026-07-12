<?php
/**
 * Base Model
 * Provides reusable CRUD operations. Child models set $table, $fillable,
 * and optionally $tenantScoped = true to auto-filter by tuition_center_id.
 */
abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected bool $tenantScoped = true; // auto add tuition_center_id filter

    public function find(int $id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $params = ['id' => $id];
        if ($this->tenantScoped && Auth::centerId() !== null) {
            $sql .= " AND tuition_center_id = :cid";
            $params['cid'] = Auth::centerId();
        }
        return Database::fetchOne($sql, $params);
    }

    public function all(array $where = [], string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        [$sql, $params] = $this->buildWhere($where);
        $sql = "SELECT * FROM {$this->table} $sql";
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        if ($limit > 0) $sql .= " LIMIT $limit OFFSET $offset";
        return Database::fetchAll($sql, $params);
    }

    public function count(array $where = []): int
    {
        [$sql, $params] = $this->buildWhere($where);
        $sql = "SELECT COUNT(*) as cnt FROM {$this->table} $sql";
        $row = Database::fetchOne($sql, $params);
        return (int) ($row['cnt'] ?? 0);
    }

    protected function buildWhere(array $where): array
    {
        $conditions = [];
        $params = [];
        if ($this->tenantScoped && Auth::centerId() !== null) {
            $conditions[] = 'tuition_center_id = :tenant_cid';
            $params['tenant_cid'] = Auth::centerId();
        }
        foreach ($where as $key => $value) {
            if (is_array($value) && isset($value[0]) && strtoupper($value[0]) === 'LIKE') {
                $conditions[] = "$key LIKE :$key";
                $params[$key] = '%' . $value[1] . '%';
            } elseif (is_array($value) && isset($value[0]) && strtoupper($value[0]) === 'IN') {
                $in = [];
                foreach ($value[1] as $i => $v) {
                    $ph = $key . '_in_' . $i;
                    $in[] = ":$ph";
                    $params[$ph] = $v;
                }
                $conditions[] = "$key IN (" . implode(',', $in) . ")";
            } elseif ($value === null) {
                $conditions[] = "$key IS NULL";
            } else {
                $conditions[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        $sql = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
        return [$sql, $params];
    }

    public function create(array $data)
    {
        $data = $this->filter($data);
        if ($this->tenantScoped && !isset($data['tuition_center_id']) && Auth::centerId() !== null) {
            $data['tuition_center_id'] = Auth::centerId();
        }
        $cols = array_keys($data);
        $placeholders = array_map(fn($c) => ":$c", $cols);
        $sql = "INSERT INTO {$this->table} (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
        Database::query($sql, $data);
        return (int) Database::lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filter($data);
        if (empty($data)) return false;
        $set = implode(',', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $sql = "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = :__id";
        if ($this->tenantScoped && Auth::centerId() !== null) {
            $sql .= " AND tuition_center_id = :__cid";
            $data['__cid'] = Auth::centerId();
        }
        $data['__id'] = $id;
        Database::query($sql, $data);
        return true;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $params = ['id' => $id];
        if ($this->tenantScoped && Auth::centerId() !== null) {
            $sql .= " AND tuition_center_id = :cid";
            $params['cid'] = Auth::centerId();
        }
        Database::query($sql, $params);
        return true;
    }

    protected function filter(array $data): array
    {
        if (empty($this->fillable)) return $data;
        return array_intersect_key($data, array_flip($this->fillable));
    }

    public function getTable(): string
    {
        return $this->table;
    }
}
