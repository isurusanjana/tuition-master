<?php
class User extends Model
{
    protected string $table = 'users';
    protected bool $tenantScoped = true;
    protected array $fillable = [
        'tuition_center_id','role_id','parent_user_id','first_name','last_name','email','phone',
        'username','password','photo','gender','dob','address','status'
    ];

    public function findByEmailOrUsername(string $value)
    {
        return Database::fetchOne("SELECT * FROM users WHERE email = :v1 OR username = :v2", ['v1' => $value, 'v2' => $value]);
    }

    /** List users with role info, scoped to tenant + optionally restricted to a role slug */
    public function listWithRole(array $filters = [], int $limit = 0, int $offset = 0): array
    {
        [$where, $params] = $this->buildListWhere($filters);
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug, r.level as role_level
                FROM users u JOIN roles r ON r.id = u.role_id $where ORDER BY u.created_at DESC";
        if ($limit > 0) $sql .= " LIMIT $limit OFFSET $offset";
        return Database::fetchAll($sql, $params);
    }

    public function countWithRole(array $filters = []): int
    {
        [$where, $params] = $this->buildListWhere($filters);
        $row = Database::fetchOne("SELECT COUNT(*) cnt FROM users u JOIN roles r ON r.id = u.role_id $where", $params);
        return (int) ($row['cnt'] ?? 0);
    }

    private function buildListWhere(array $filters): array
    {
        $conditions = [];
        $params = [];
        if (!Auth::isSuperAdmin() && Auth::centerId() !== null) {
            $conditions[] = 'u.tuition_center_id = :cid';
            $params['cid'] = Auth::centerId();
        }
        if (!empty($filters['role_slug'])) {
            $conditions[] = 'r.slug = :rslug';
            $params['rslug'] = $filters['role_slug'];
        }
        if (!empty($filters['search'])) {
            $conditions[] = '(u.first_name LIKE :s1 OR u.last_name LIKE :s2 OR u.email LIKE :s3 OR u.username LIKE :s4)';
            $like = '%' . $filters['search'] . '%';
            $params['s1'] = $like; $params['s2'] = $like; $params['s3'] = $like; $params['s4'] = $like;
        }
        if (!empty($filters['under_user_id'])) {
            $ids = Auth::subordinateIds($filters['under_user_id']);
            $ids[] = $filters['under_user_id'];
            $in = [];
            foreach ($ids as $i => $id) { $ph = "uid$i"; $in[] = ":$ph"; $params[$ph] = $id; }
            $conditions[] = 'u.id IN (' . implode(',', $in) . ')';
        }
        $sql = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
        return [$sql, $params];
    }

    public function findWithRole(int $id)
    {
        return Database::fetchOne(
            "SELECT u.*, r.name as role_name, r.slug as role_slug, r.level as role_level
             FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id", ['id' => $id]
        );
    }
}
