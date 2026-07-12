<?php
/**
 * Permission - resolves effective menu visibility and CRUD action permissions.
 * Resolution order: user-level override (user_menu_permission / user_permission)
 * takes precedence over the role's default (role_menu / role_permission).
 */
class Permission
{
    private static array $menuCache = [];
    private static array $permCache = [];

    public static function menus(): array
    {
        $uid = Auth::id();
        if ($uid === null) return [];
        if (isset(self::$menuCache[$uid])) return self::$menuCache[$uid];

        if (Auth::isSuperAdmin()) {
            $rows = Database::fetchAll("SELECT * FROM menu_items WHERE is_active = 1 ORDER BY sort_order");
            return self::$menuCache[$uid] = self::buildTree($rows);
        }

        $roleId = Auth::user()['role_id'];
        $rows = Database::fetchAll(
            "SELECT m.* FROM menu_items m
             JOIN role_menu rm ON rm.menu_item_id = m.id
             WHERE rm.role_id = :rid AND m.is_active = 1
             AND m.id NOT IN (
                SELECT menu_item_id FROM user_menu_permission WHERE user_id = :uid AND allowed = 0
             )
             UNION
             SELECT m.* FROM menu_items m
             JOIN user_menu_permission ump ON ump.menu_item_id = m.id
             WHERE ump.user_id = :uid2 AND ump.allowed = 1 AND m.is_active = 1
             ORDER BY sort_order",
            ['rid' => $roleId, 'uid' => $uid, 'uid2' => $uid]
        );
        return self::$menuCache[$uid] = self::buildTree($rows);
    }

    private static function buildTree(array $rows): array
    {
        $byId = [];
        foreach ($rows as $r) $byId[$r['id']] = $r + ['children' => []];
        $tree = [];
        foreach ($byId as $id => $row) {
            if ($row['parent_id'] && isset($byId[$row['parent_id']])) {
                $byId[$row['parent_id']]['children'][] = &$byId[$id];
            } else {
                $tree[] = &$byId[$id];
            }
        }
        return $tree;
    }

    /** Clears cached menu/permission resolutions. Call after modifying role/user permissions within the same request (and used by tests). */
    public static function clearCache(): void
    {
        self::$menuCache = [];
        self::$permCache = [];
    }

    public static function can(string $moduleKey, string $action): bool
    {
        if (Auth::isSuperAdmin()) return true;
        $uid = Auth::id();
        if ($uid === null) return false;

        $cacheKey = "$uid:$moduleKey:$action";
        if (isset(self::$permCache[$cacheKey])) return self::$permCache[$cacheKey];

        // 1. user-level explicit override
        $override = Database::fetchOne(
            "SELECT up.allowed FROM user_permission up
             JOIN permissions p ON p.id = up.permission_id
             WHERE up.user_id = :uid AND p.module_key = :m AND p.action = :a",
            ['uid' => $uid, 'm' => $moduleKey, 'a' => $action]
        );
        if ($override !== null) {
            return self::$permCache[$cacheKey] = (bool) $override['allowed'];
        }

        // 2. role default
        $roleId = Auth::user()['role_id'];
        $row = Database::fetchOne(
            "SELECT rp.id FROM role_permission rp
             JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = :rid AND p.module_key = :m AND p.action = :a",
            ['rid' => $roleId, 'm' => $moduleKey, 'a' => $action]
        );
        return self::$permCache[$cacheKey] = (bool) $row;
    }

    /** Throws / redirects if user cannot perform the action. Call at top of controller methods. */
    public static function authorize(string $moduleKey, string $action): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }
        if (!self::can($moduleKey, $action)) {
            http_response_code(403);
            View::render('errors/403', ['module' => $moduleKey, 'action' => $action]);
            exit;
        }
    }
}
