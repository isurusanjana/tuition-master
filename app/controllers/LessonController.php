<?php
class LessonController extends Controller
{
    public function index(): void
    {
        $this->authorize('lessons', 'view');
        if (Auth::roleSlug() === 'student') {
            $lessons = (new Lesson())->forUser(Auth::id());
        } else {
            $lessons = (new Lesson())->all([], 'created_at DESC');
        }
        $this->view('lessons/index', ['title' => 'Lesson Tools', 'lessons' => $lessons]);
    }

    public function create(): void
    {
        $this->authorize('lessons', 'add');
        $classes = (new ClassRoom())->all([], 'name');
        $this->view('lessons/create', ['title' => 'Add Lesson', 'classes' => $classes]);
    }

    public function store(): void
    {
        $this->authorize('lessons', 'add');
        $this->validateCsrf();
        $data = Request::only(['class_id','title','description','resource_type','external_url']);
        $v = new Validator($data);
        $v->required('title')->required('resource_type');
        if ($v->fails()) redirect_with_error('/lessons/create', $v->firstError());

        $filePath = null;
        $file = Request::file('resource_file');
        if ($file) {
            $allowed = match ($data['resource_type']) {
                'pdf' => ['pdf'],
                'video' => ['mp4', 'mov', 'avi', 'webm'],
                default => ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'pdf'],
            };
            $filePath = FileUpload::upload($file, 'lessons', $allowed, 100);
            if (!$filePath) redirect_with_error('/lessons/create', 'File upload failed. Check file type/size.');
        }

        $data['class_id'] = $data['class_id'] ?: null;
        $id = (new Lesson())->create($data + ['file_path' => $filePath, 'uploaded_by' => Auth::id()]);
        log_activity('create', 'lessons', "Added lesson: {$data['title']}");
        redirect_with_success("/lessons/$id/assign", 'Lesson uploaded. Now assign it to your students.');
    }

    public function show(int $id): void
    {
        $this->authorize('lessons', 'view');
        $lesson = (new Lesson())->find($id);
        if (!$lesson) Response::redirect('/lessons');

        if (Auth::roleSlug() === 'student') {
            Database::query("UPDATE lesson_assignments SET is_viewed = 1 WHERE lesson_id = :l AND user_id = :u", ['l' => $id, 'u' => Auth::id()]);
        }
        $this->view('lessons/show', ['title' => $lesson['title'], 'lesson' => $lesson]);
    }

    public function edit(int $id): void
    {
        $this->authorize('lessons', 'edit');
        $lesson = (new Lesson())->find($id);
        if (!$lesson) Response::redirect('/lessons');
        $classes = (new ClassRoom())->all([], 'name');
        $this->view('lessons/edit', ['title' => 'Edit Lesson', 'lesson' => $lesson, 'classes' => $classes]);
    }

    public function update(int $id): void
    {
        $this->authorize('lessons', 'edit');
        $this->validateCsrf();
        $lesson = (new Lesson())->find($id);
        if (!$lesson) Response::redirect('/lessons');

        $data = Request::only(['class_id','title','description','resource_type','external_url']);
        $data['class_id'] = $data['class_id'] ?: null;

        $file = Request::file('resource_file');
        if ($file) {
            $allowed = match ($data['resource_type']) {
                'pdf' => ['pdf'],
                'video' => ['mp4', 'mov', 'avi', 'webm'],
                default => ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'pdf'],
            };
            $newPath = FileUpload::upload($file, 'lessons', $allowed, 100);
            if ($newPath) {
                FileUpload::delete($lesson['file_path']);
                $data['file_path'] = $newPath;
            }
        }
        (new Lesson())->update($id, $data);
        redirect_with_success("/lessons/$id", 'Lesson updated.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('lessons', 'delete');
        $this->validateCsrf();
        $lesson = (new Lesson())->find($id);
        if ($lesson) FileUpload::delete($lesson['file_path']);
        (new Lesson())->delete($id);
        redirect_with_success('/lessons', 'Lesson deleted.');
    }

    public function assignForm(int $id): void
    {
        $this->authorize('lessons', 'assign');
        $lesson = (new Lesson())->find($id);
        if (!$lesson) Response::redirect('/lessons');
        $students = Database::fetchAll(
            "SELECT u.* FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='student' AND u.tuition_center_id=:c ORDER BY u.first_name",
            ['c' => Auth::centerId()]
        );
        $assignedUsers = (new Lesson())->assignedUsers($id);
        $assignedIds = array_column($assignedUsers, 'id');
        $this->view('lessons/assign', ['title' => 'Assign Lesson', 'lesson' => $lesson, 'students' => $students, 'assignedIds' => $assignedIds]);
    }

    public function assign(int $id): void
    {
        $this->authorize('lessons', 'assign');
        $this->validateCsrf();
        $userIds = Request::input('user_ids', []);
        (new Lesson())->assign($id, array_map('intval', (array) $userIds), Auth::id());
        log_activity('assign', 'lessons', "Assigned lesson #$id to " . count($userIds) . " user(s)");
        redirect_with_success("/lessons/$id", 'Lesson assigned successfully.');
    }
}
