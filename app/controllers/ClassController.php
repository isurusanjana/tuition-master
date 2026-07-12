<?php
class ClassController extends Controller
{
    public function index(): void
    {
        $this->authorize('classes', 'view');
        $model = new ClassRoom();
        if (Auth::roleSlug() === 'teacher') {
            $classes = $model->classesForTeacher(Auth::id());
        } elseif (Auth::roleSlug() === 'student') {
            $classes = $model->classesForStudent(Auth::id());
        } else {
            $search = Request::input('search', '');
            $where = $search ? ['name' => ['LIKE', $search]] : [];
            $classes = $model->all($where, 'created_at DESC');
        }
        $this->view('classes/index', ['title' => 'Classes', 'classes' => $classes]);
    }

    public function create(): void
    {
        $this->authorize('classes', 'add');
        $this->view('classes/create', ['title' => 'Add Class']);
    }

    public function store(): void
    {
        $this->authorize('classes', 'add');
        $this->validateCsrf();
        $data = Request::only(['name','grade','subject','description','schedule','capacity']);
        $v = new Validator($data);
        $v->required('name');
        if ($v->fails()) redirect_with_error('/classes/create', $v->firstError());

        $model = new ClassRoom();
        $id = $model->create($data + ['status' => 'active', 'created_by' => Auth::id()]);
        log_activity('create', 'classes', "Created class: {$data['name']}");
        redirect_with_success("/classes/$id", 'Class created. Now assign teachers and students.');
    }

    public function show(int $id): void
    {
        $this->authorize('classes', 'view');
        $model = new ClassRoom();
        $class = $model->find($id);
        if (!$class) Response::redirect('/classes');
        $teachers = $model->teachers($id);
        $students = $model->students($id);

        $availableTeachers = Database::fetchAll(
            "SELECT u.* FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='teacher' AND u.tuition_center_id=:c",
            ['c' => Auth::centerId()]
        );
        $availableStudents = Database::fetchAll(
            "SELECT u.* FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='student' AND u.tuition_center_id=:c",
            ['c' => Auth::centerId()]
        );

        $this->view('classes/show', [
            'title' => $class['name'], 'class' => $class, 'teachers' => $teachers, 'students' => $students,
            'availableTeachers' => $availableTeachers, 'availableStudents' => $availableStudents,
        ]);
    }

    public function edit(int $id): void
    {
        $this->authorize('classes', 'edit');
        $model = new ClassRoom();
        $class = $model->find($id);
        if (!$class) Response::redirect('/classes');
        $this->view('classes/edit', ['title' => 'Edit Class', 'class' => $class]);
    }

    public function update(int $id): void
    {
        $this->authorize('classes', 'edit');
        $this->validateCsrf();
        $data = Request::only(['name','grade','subject','description','schedule','capacity','status']);
        (new ClassRoom())->update($id, $data);
        log_activity('update', 'classes', "Updated class #$id");
        redirect_with_success('/classes', 'Class updated.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('classes', 'delete');
        $this->validateCsrf();
        (new ClassRoom())->delete($id);
        log_activity('delete', 'classes', "Deleted class #$id");
        redirect_with_success('/classes', 'Class deleted.');
    }

    public function assignTeacher(int $id): void
    {
        $this->authorize('classes', 'assign');
        $this->validateCsrf();
        $teacherId = (int) Request::input('teacher_id');
        (new ClassRoom())->addTeacher($id, $teacherId);
        redirect_with_success("/classes/$id", 'Teacher assigned.');
    }

    public function assignStudent(int $id): void
    {
        $this->authorize('classes', 'assign');
        $this->validateCsrf();
        $studentId = (int) Request::input('student_id');
        (new ClassRoom())->addStudent($id, $studentId);
        redirect_with_success("/classes/$id", 'Student enrolled.');
    }

    public function removeTeacher(int $id, int $tid): void
    {
        $this->authorize('classes', 'assign');
        $this->validateCsrf();
        (new ClassRoom())->removeTeacher($id, $tid);
        redirect_with_success("/classes/$id", 'Teacher removed.');
    }

    public function removeStudent(int $id, int $sid): void
    {
        $this->authorize('classes', 'assign');
        $this->validateCsrf();
        (new ClassRoom())->removeStudent($id, $sid);
        redirect_with_success("/classes/$id", 'Student removed.');
    }
}
