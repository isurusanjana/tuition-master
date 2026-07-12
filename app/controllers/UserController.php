<?php
class UserController extends Controller
{
    public function index(): void
    {
        $this->authorize('users', 'view');
        $model = new User();
        $filters = [
            'role_slug' => Request::input('role', ''),
            'search' => Request::input('search', ''),
        ];
        // Non-super-admins only ever see users within their own tenant (handled in model).
        // Additionally restrict to "my subtree" unless the user is a center_admin (who can see everyone in the tenant).
        if (!Auth::isSuperAdmin() && Auth::roleSlug() !== 'center_admin') {
            $filters['under_user_id'] = Auth::id();
        }
        $users = $model->listWithRole(array_filter($filters));
        $roles = (new Role())->assignableRoles();
        $this->view('users/index', ['title' => 'Users', 'users' => $users, 'roles' => $roles, 'filters' => $filters]);
    }

    public function create(): void
    {
        $this->authorize('users', 'add');
        $roles = (new Role())->assignableRoles();
        $centers = Auth::isSuperAdmin() ? (new TuitionCenter())->all([], 'name ASC') : [];
        $this->view('users/create', ['title' => 'Add User', 'roles' => $roles, 'centers' => $centers]);
    }

    public function store(): void
    {
        $this->authorize('users', 'add');
        $this->validateCsrf();

        $data = Request::only(['first_name','last_name','email','phone','username','password','gender','dob','address','role_id']);
        $v = new Validator($data);
        $v->required('first_name')->required('email')->email('email')
          ->required('username')->required('password')->min('password', 6)
          ->required('role_id')
          ->unique('email', 'users', 'email')
          ->unique('username', 'users', 'username');

        if ($v->fails()) {
            Session::flash('errors', implode(' ', array_map(fn($e) => $e[0], $v->errors())));
            Response::back('/users/create');
        }

        // security: the chosen role must be one this user is allowed to assign
        $assignable = array_column((new Role())->assignableRoles(), 'id');
        if (!in_array((int) $data['role_id'], $assignable)) {
            redirect_with_error('/users/create', 'You are not allowed to assign that role.');
        }

        $centerId = Auth::isSuperAdmin() ? (int) Request::input('tuition_center_id') : Auth::centerId();

        $userModel = new User();
        $newId = $userModel->create([
            'tuition_center_id' => $centerId,
            'role_id' => $data['role_id'],
            'parent_user_id' => Auth::id(),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'gender' => $data['gender'] ?: null,
            'dob' => $data['dob'] ?: null,
            'address' => $data['address'] ?? null,
            'status' => 'active',
        ]);

        // link parent to student(s) if provided
        $studentIds = Request::input('link_student_ids', []);
        if (is_array($studentIds)) {
            foreach ($studentIds as $sid) {
                Database::query("INSERT IGNORE INTO student_parent (student_id, parent_id) VALUES (:s,:p)", ['s' => (int) $sid, 'p' => $newId]);
            }
        }

        log_activity('create', 'users', "Created user #$newId ({$data['email']})");
        redirect_with_success('/users', 'User created successfully.');
    }

    public function show(int $id): void
    {
        $this->authorize('users', 'view');
        $model = new User();
        $user = $model->findWithRole($id);
        $this->guardUser($user);
        $this->view('users/show', ['title' => $user['first_name'] . ' ' . $user['last_name'], 'user' => $user]);
    }

    public function edit(int $id): void
    {
        $this->authorize('users', 'edit');
        $model = new User();
        $user = $model->findWithRole($id);
        $this->guardUser($user);
        $roles = (new Role())->assignableRoles();
        $this->view('users/edit', ['title' => 'Edit User', 'user' => $user, 'roles' => $roles]);
    }

    public function update(int $id): void
    {
        $this->authorize('users', 'edit');
        $this->validateCsrf();
        $model = new User();
        $user = $model->findWithRole($id);
        $this->guardUser($user);

        $data = Request::only(['first_name','last_name','email','phone','gender','dob','address','status','role_id']);
        if (!empty($data['role_id'])) {
            $assignable = array_column((new Role())->assignableRoles(), 'id');
            if (!in_array((int) $data['role_id'], $assignable) && (int) $data['role_id'] !== (int) $user['role_id']) {
                unset($data['role_id']);
            }
        }
        $newPassword = Request::input('password');
        if ($newPassword) {
            $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $model->update($id, $data);
        log_activity('update', 'users', "Updated user #$id");
        redirect_with_success('/users', 'User updated successfully.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('users', 'delete');
        $this->validateCsrf();
        $model = new User();
        $user = $model->findWithRole($id);
        $this->guardUser($user);
        if ((int) $id === (int) Auth::id()) {
            redirect_with_error('/users', 'You cannot delete your own account.');
        }
        Database::query("DELETE FROM users WHERE id = :id", ['id' => $id]);
        log_activity('delete', 'users', "Deleted user #$id");
        redirect_with_success('/users', 'User deleted.');
    }

    /** Fine-grained menu + CRUD permission overrides for a single user */
    public function permissions(int $id): void
    {
        $this->authorize('roles', 'assign_permission');
        $model = new User();
        $user = $model->findWithRole($id);
        $this->guardUser($user);

        $menus = Database::fetchAll("SELECT * FROM menu_items WHERE is_active = 1 ORDER BY sort_order");
        $allPermissions = Database::fetchAll("SELECT * FROM permissions ORDER BY module_key, action");
        $userMenuOverrides = Database::fetchAll("SELECT menu_item_id, allowed FROM user_menu_permission WHERE user_id = :u", ['u' => $id]);
        $userPermOverrides = Database::fetchAll("SELECT permission_id, allowed FROM user_permission WHERE user_id = :u", ['u' => $id]);

        $menuMap = [];
        foreach ($userMenuOverrides as $m) $menuMap[$m['menu_item_id']] = (bool) $m['allowed'];
        $permMap = [];
        foreach ($userPermOverrides as $p) $permMap[$p['permission_id']] = (bool) $p['allowed'];

        $this->view('users/permissions', [
            'title' => 'Permissions - ' . $user['first_name'],
            'user' => $user, 'menus' => $menus, 'permissions' => $allPermissions,
            'menuMap' => $menuMap, 'permMap' => $permMap,
        ]);
    }

    public function savePermissions(int $id): void
    {
        $this->authorize('roles', 'assign_permission');
        $this->validateCsrf();
        $model = new User();
        $user = $model->findWithRole($id);
        $this->guardUser($user);

        $menuAllowed = Request::input('menu_allowed', []);   // menu_item_id => 1
        $menuDenied = Request::input('menu_denied', []);     // menu_item_id => 1
        $permAllowed = Request::input('perm_allowed', []);
        $permDenied = Request::input('perm_denied', []);

        Database::query("DELETE FROM user_menu_permission WHERE user_id = :u", ['u' => $id]);
        Database::query("DELETE FROM user_permission WHERE user_id = :u", ['u' => $id]);

        foreach ((array) $menuAllowed as $menuId => $v) {
            Database::query("INSERT INTO user_menu_permission (user_id, menu_item_id, allowed) VALUES (:u,:m,1)", ['u' => $id, 'm' => $menuId]);
        }
        foreach ((array) $menuDenied as $menuId => $v) {
            Database::query("INSERT INTO user_menu_permission (user_id, menu_item_id, allowed) VALUES (:u,:m,0)
                ON DUPLICATE KEY UPDATE allowed = 0", ['u' => $id, 'm' => $menuId]);
        }
        foreach ((array) $permAllowed as $permId => $v) {
            Database::query("INSERT INTO user_permission (user_id, permission_id, allowed) VALUES (:u,:p,1)", ['u' => $id, 'p' => $permId]);
        }
        foreach ((array) $permDenied as $permId => $v) {
            Database::query("INSERT INTO user_permission (user_id, permission_id, allowed) VALUES (:u,:p,0)
                ON DUPLICATE KEY UPDATE allowed = 0", ['u' => $id, 'p' => $permId]);
        }

        Permission::clearCache();
        log_activity('update', 'roles', "Updated permission overrides for user #$id");
        redirect_with_success("/users/$id/permissions", 'Permissions updated successfully.');
    }
}
