<?php
class TuitionCenterController extends Controller
{
    private function guardSuperAdmin(): void
    {
        if (!Auth::isSuperAdmin()) {
            http_response_code(403);
            View::render('errors/403', ['module' => 'tuition_centers', 'action' => 'view']);
            exit;
        }
    }

    public function index(): void
    {
        $this->guardSuperAdmin();
        $this->authorize('tuition_centers', 'view');
        $model = new TuitionCenter();
        $search = Request::input('search', '');
        $where = $search ? ['name' => ['LIKE', $search]] : [];
        $centers = $model->all($where, 'created_at DESC');
        $this->view('tuition_centers/index', ['title' => 'Tuition Centers', 'centers' => $centers, 'search' => $search]);
    }

    public function create(): void
    {
        $this->guardSuperAdmin();
        $this->authorize('tuition_centers', 'add');
        $this->view('tuition_centers/create', ['title' => 'Add Tuition Center']);
    }

    public function store(): void
    {
        $this->guardSuperAdmin();
        $this->authorize('tuition_centers', 'add');
        $this->validateCsrf();

        $data = Request::only(['name','code','email','phone','address']);
        $adminData = Request::only(['admin_first_name','admin_last_name','admin_email','admin_username','admin_password']);

        $v = new Validator($data + $adminData);
        $v->required('name')->required('code')->required('email')->email('email')
          ->unique('code', 'tuition_centers', 'code')
          ->required('admin_first_name')->required('admin_email')->email('admin_email')
          ->required('admin_username')->required('admin_password')->min('admin_password', 6)
          ->unique('admin_email', 'users', 'email')
          ->unique('admin_username', 'users', 'username');

        if ($v->fails()) {
            Session::flash('errors', implode(' ', array_map(fn($e) => $e[0], $v->errors())));
            Response::back('/tuition-centers/create');
        }

        $centerModel = new TuitionCenter();
        $centerId = $centerModel->create([
            'name' => $data['name'], 'code' => $data['code'], 'email' => $data['email'],
            'phone' => $data['phone'] ?? null, 'address' => $data['address'] ?? null,
            'status' => 'active', 'created_by' => Auth::id(),
        ]);

        // create the Tuition Center Admin user for this new center
        $roleRow = Database::fetchOne("SELECT id FROM roles WHERE slug = 'center_admin' AND tuition_center_id IS NULL");
        $userModel = new User();
        $userModel->create([
            'tuition_center_id' => $centerId,
            'role_id' => $roleRow['id'],
            'parent_user_id' => Auth::id(),
            'first_name' => $adminData['admin_first_name'],
            'last_name' => $adminData['admin_last_name'] ?? '',
            'email' => $adminData['admin_email'],
            'username' => $adminData['admin_username'],
            'password' => password_hash($adminData['admin_password'], PASSWORD_BCRYPT),
            'status' => 'active',
        ]);

        log_activity('create', 'tuition_centers', "Created tuition center: {$data['name']}");
        redirect_with_success('/tuition-centers', 'Tuition center and admin account created successfully.');
    }

    public function show(int $id): void
    {
        $this->guardSuperAdmin();
        $this->authorize('tuition_centers', 'view');
        $model = new TuitionCenter();
        $center = Database::fetchOne("SELECT * FROM tuition_centers WHERE id = :id", ['id' => $id]);
        if (!$center) { Response::redirect('/tuition-centers'); }
        $admins = Database::fetchAll(
            "SELECT u.* FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='center_admin' AND u.tuition_center_id = :c",
            ['c' => $id]
        );
        $counts = [
            'users' => Database::fetchOne("SELECT COUNT(*) c FROM users WHERE tuition_center_id=:c", ['c' => $id])['c'],
            'classes' => Database::fetchOne("SELECT COUNT(*) c FROM classes WHERE tuition_center_id=:c", ['c' => $id])['c'],
        ];
        $this->view('tuition_centers/show', ['title' => $center['name'], 'center' => $center, 'admins' => $admins, 'counts' => $counts]);
    }

    public function edit(int $id): void
    {
        $this->guardSuperAdmin();
        $this->authorize('tuition_centers', 'edit');
        $center = Database::fetchOne("SELECT * FROM tuition_centers WHERE id = :id", ['id' => $id]);
        if (!$center) Response::redirect('/tuition-centers');
        $this->view('tuition_centers/edit', ['title' => 'Edit ' . $center['name'], 'center' => $center]);
    }

    public function update(int $id): void
    {
        $this->guardSuperAdmin();
        $this->authorize('tuition_centers', 'edit');
        $this->validateCsrf();
        $data = Request::only(['name','email','phone','address','status']);
        $model = new TuitionCenter();
        $model->update($id, $data);
        log_activity('update', 'tuition_centers', "Updated tuition center #$id");
        redirect_with_success('/tuition-centers', 'Tuition center updated.');
    }

    public function destroy(int $id): void
    {
        $this->guardSuperAdmin();
        $this->authorize('tuition_centers', 'delete');
        $this->validateCsrf();
        $model = new TuitionCenter();
        $model->delete($id);
        log_activity('delete', 'tuition_centers', "Deleted tuition center #$id");
        redirect_with_success('/tuition-centers', 'Tuition center deleted.');
    }
}
