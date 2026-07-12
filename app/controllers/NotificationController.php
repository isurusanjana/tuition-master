<?php
class NotificationController extends Controller
{
    public function index(): void
    {
        $this->authorize('notifications', 'view');
        $centerId = Auth::centerId();
        $sql = "SELECT n.* FROM notifications n WHERE " . ($centerId ? "n.tuition_center_id = :c" : "n.tuition_center_id IS NULL");
        $sql .= " ORDER BY n.created_at DESC";
        $notifications = Database::fetchAll($sql, $centerId ? ['c' => $centerId] : []);
        $this->view('notifications/index', ['title' => 'Notifications', 'notifications' => $notifications]);
    }

    public function create(): void
    {
        $this->authorize('notifications', 'add');
        $roles = Database::fetchAll("SELECT * FROM roles WHERE tuition_center_id IS NULL OR tuition_center_id = :c", ['c' => Auth::centerId()]);
        $this->view('notifications/create', ['title' => 'New Notification', 'roles' => $roles]);
    }

    public function store(): void
    {
        $this->authorize('notifications', 'add');
        $this->validateCsrf();
        $data = Request::only(['title','message','type','target_role_id','starts_at','ends_at']);
        $v = new Validator($data);
        $v->required('title')->required('message');
        if ($v->fails()) redirect_with_error('/notifications/create', $v->firstError());

        $data['target_role_id'] = $data['target_role_id'] ?: null;
        // Only super admin can broadcast system-wide (NULL tenant); others scoped to their own center
        $centerId = Auth::isSuperAdmin() && Request::input('broadcast_all') ? null : Auth::centerId();

        (new Notification())->create($data + ['tuition_center_id' => $centerId, 'created_by' => Auth::id()]);
        log_activity('create', 'notifications', "Posted notification: {$data['title']}");
        redirect_with_success('/notifications', 'Notification posted.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('notifications', 'delete');
        $this->validateCsrf();
        Database::query("DELETE FROM notifications WHERE id = :id", ['id' => $id]);
        redirect_with_success('/notifications', 'Notification removed.');
    }

    public function markRead(int $id): void
    {
        (new Notification())->markRead($id, Auth::id());
        Response::json(['ok' => true]);
    }
}
