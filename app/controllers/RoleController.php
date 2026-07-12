<?php
class RoleController extends Controller
{
    public function index(): void
    {
        $this->authorize('roles', 'view');
        $sql = "SELECT * FROM roles WHERE tuition_center_id IS NULL";
        $params = [];
        if (!Auth::isSuperAdmin()) {
            $sql .= " OR tuition_center_id = :c";
            $params['c'] = Auth::centerId();
        }
        $sql .= " ORDER BY level ASC";
        $roles = Database::fetchAll($sql, $params);
        $this->view('roles/index', ['title' => 'Roles & Permissions', 'roles' => $roles]);
    }

    public function create(): void
    {
        $this->authorize('roles', 'add');
        $this->view('roles/create', ['title' => 'Add Custom Role']);
    }

    public function store(): void
    {
        $this->authorize('roles', 'add');
        $this->validateCsrf();
        $data = Request::only(['name','description']);
        $v = new Validator($data);
        $v->required('name');
        if ($v->fails()) redirect_with_error('/roles/create', $v->firstError());

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $data['name'])) . '_' . substr(md5(uniqid()), 0, 4);
        $model = new Role();
        $newId = $model->create([
            'tuition_center_id' => Auth::centerId(),
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? '',
            'level' => Auth::roleLevel() + 1,
            'is_system' => 0,
        ]);
        log_activity('create', 'roles', "Created custom role: {$data['name']}");
        redirect_with_success("/roles/$newId/access", 'Role created. Now configure its menu & permission access.');
    }

    public function edit(int $id): void
    {
        $this->authorize('roles', 'edit');
        $role = Database::fetchOne("SELECT * FROM roles WHERE id = :id", ['id' => $id]);
        if (!$role || (int) $role['is_system'] === 1) redirect_with_error('/roles', 'System roles cannot be edited directly.');
        $this->view('roles/edit', ['title' => 'Edit Role', 'role' => $role]);
    }

    public function update(int $id): void
    {
        $this->authorize('roles', 'edit');
        $this->validateCsrf();
        $data = Request::only(['name','description']);
        $model = new Role();
        Database::query("UPDATE roles SET name=:n, description=:d WHERE id=:id AND is_system = 0", ['n' => $data['name'], 'd' => $data['description'] ?? '', 'id' => $id]);
        redirect_with_success('/roles', 'Role updated.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('roles', 'delete');
        $this->validateCsrf();
        $role = Database::fetchOne("SELECT * FROM roles WHERE id = :id", ['id' => $id]);
        if (!$role || (int) $role['is_system'] === 1) redirect_with_error('/roles', 'System roles cannot be deleted.');
        Database::query("DELETE FROM roles WHERE id = :id", ['id' => $id]);
        redirect_with_success('/roles', 'Role deleted.');
    }

    /** Configure which menu items + CRUD permissions this role gets by default */
    public function access(int $id): void
    {
        $this->authorize('roles', 'assign_permission');
        $role = Database::fetchOne("SELECT * FROM roles WHERE id = :id", ['id' => $id]);
        if (!$role) Response::redirect('/roles');
        $menus = Database::fetchAll("SELECT * FROM menu_items WHERE is_active = 1 ORDER BY sort_order");
        $permissions = Database::fetchAll("SELECT * FROM permissions ORDER BY module_key, action");
        $roleMenuIds = array_column(Database::fetchAll("SELECT menu_item_id FROM role_menu WHERE role_id = :r", ['r' => $id]), 'menu_item_id');
        $rolePermIds = array_column(Database::fetchAll("SELECT permission_id FROM role_permission WHERE role_id = :r", ['r' => $id]), 'permission_id');
        $this->view('roles/access', [
            'title' => 'Configure Access - ' . $role['name'], 'role' => $role, 'menus' => $menus,
            'permissions' => $permissions, 'roleMenuIds' => $roleMenuIds, 'rolePermIds' => $rolePermIds,
        ]);
    }

    public function saveAccess(int $id): void
    {
        $this->authorize('roles', 'assign_permission');
        $this->validateCsrf();
        $menuIds = Request::input('menu_ids', []);
        $permIds = Request::input('perm_ids', []);

        Database::query("DELETE FROM role_menu WHERE role_id = :r", ['r' => $id]);
        Database::query("DELETE FROM role_permission WHERE role_id = :r", ['r' => $id]);
        foreach ((array) $menuIds as $mid) {
            Database::query("INSERT INTO role_menu (role_id, menu_item_id) VALUES (:r,:m)", ['r' => $id, 'm' => $mid]);
        }
        foreach ((array) $permIds as $pid) {
            Database::query("INSERT INTO role_permission (role_id, permission_id) VALUES (:r,:p)", ['r' => $id, 'p' => $pid]);
        }
        Permission::clearCache();
        log_activity('update', 'roles', "Updated default access for role #$id");
        redirect_with_success('/roles', 'Role access updated.');
    }
}
