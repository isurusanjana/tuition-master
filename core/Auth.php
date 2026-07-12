<?php
/**
 * Auth - authentication + multi-tenant + hierarchy helper.
 *
 * Data isolation rules implemented here:
 *  - Super Admin (tuition_center_id NULL, role level 0) sees ALL data.
 *  - Every other user is bound to a single tuition_center_id and can only
 *    ever query/act within that tenant (enforced in Model::tenantScoped).
 *  - Within a tenant, a user can only manage users/records that are
 *    "under" them in the parent_user_id hierarchy (see canManageUser()).
 */
class Auth
{
    private static ?array $user = null;

    public static function attempt(string $username, string $password): bool
    {
        $row = Database::fetchOne(
            "SELECT u.*, r.slug as role_slug, r.level as role_level, r.name as role_name
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE (u.username = :u1 OR u.email = :u2) AND u.status = 'active' LIMIT 1",
            ['u1' => $username, 'u2' => $username]
        );

        if (!$row || !password_verify($password, $row['password'])) {
            return false;
        }

        Database::query("UPDATE users SET last_login = NOW() WHERE id = :id", ['id' => $row['id']]);

        Session::set('user_id', (int) $row['id']);
        Session::set('user', $row);
        self::$user = $row;
        return true;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$user = null;
    }

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function user(): ?array
    {
        if (self::$user === null && Session::has('user')) {
            self::$user = Session::get('user');
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function centerId(): ?int
    {
        $u = self::user();
        if (!$u) return null;
        // Super admin has no tenant restriction
        if (($u['role_slug'] ?? '') === 'super_admin') return null;
        return $u['tuition_center_id'] !== null ? (int) $u['tuition_center_id'] : null;
    }

    public static function isSuperAdmin(): bool
    {
        return (self::user()['role_slug'] ?? '') === 'super_admin';
    }

    public static function roleSlug(): ?string
    {
        return self::user()['role_slug'] ?? null;
    }

    public static function roleLevel(): int
    {
        return (int) (self::user()['role_level'] ?? 999);
    }

    /**
     * Determine whether the logged-in user is allowed to manage (view/edit/delete)
     * the given target user, based on tenant isolation + hierarchy (parent_user_id chain)
     * + role level (cannot manage someone with equal/higher authority unless super admin).
     */
    public static function canManageUser(array $target): bool
    {
        if (self::isSuperAdmin()) return true;

        $me = self::user();
        if (!$me) return false;

        // must belong to the same tuition center
        if ((int) ($target['tuition_center_id'] ?? 0) !== (int) $me['tuition_center_id']) {
            return false;
        }

        // cannot manage a user with equal or higher authority (lower/equal level number)
        if ((int) ($target['role_level'] ?? 999) <= (int) $me['role_level']) {
            return (int) $target['id'] === (int) $me['id']; // can manage self only
        }

        // must be within the creation hierarchy chain (walk up parent_user_id)
        return self::isDescendantOf((int) $target['id'], (int) $me['id']);
    }

    private static function isDescendantOf(int $userId, int $ancestorId, int $depth = 0): bool
    {
        if ($depth > 10) return false; // safety cap
        $row = Database::fetchOne("SELECT parent_user_id FROM users WHERE id = :id", ['id' => $userId]);
        if (!$row || $row['parent_user_id'] === null) return false;
        if ((int) $row['parent_user_id'] === $ancestorId) return true;
        return self::isDescendantOf((int) $row['parent_user_id'], $ancestorId, $depth + 1);
    }

    /**
     * Returns array of all user IDs "under" the logged-in user (their full subtree),
     * used to scope lists like "students I can assign lessons/exams to".
     */
    public static function subordinateIds(?int $rootId = null): array
    {
        $rootId = $rootId ?? self::id();
        if ($rootId === null) return [];
        $ids = [];
        $queue = [$rootId];
        while ($queue) {
            $current = array_shift($queue);
            $children = Database::fetchAll("SELECT id FROM users WHERE parent_user_id = :p", ['p' => $current]);
            foreach ($children as $c) {
                $ids[] = (int) $c['id'];
                $queue[] = (int) $c['id'];
            }
        }
        return $ids;
    }
}
