<?php
class NoteController extends Controller
{
    public function index(): void
    {
        $this->authorize('notes', 'view');
        $model = new Note();
        $studentId = Request::input('student_id', '');
        $where = $studentId ? ['student_id' => (int) $studentId] : [];
        $notes = $model->all($where, 'created_at DESC');
        // attach student names
        foreach ($notes as &$n) {
            if ($n['student_id']) {
                $s = Database::fetchOne("SELECT first_name, last_name FROM users WHERE id = :id", ['id' => $n['student_id']]);
                $n['student_name'] = $s ? $s['first_name'] . ' ' . $s['last_name'] : '';
            }
        }
        $this->view('notes/index', ['title' => 'Special Notes', 'notes' => $notes]);
    }

    public function create(): void
    {
        $this->authorize('notes', 'add');
        $students = Database::fetchAll(
            "SELECT u.* FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='student' AND u.tuition_center_id=:c",
            ['c' => Auth::centerId()]
        );
        $classes = (new ClassRoom())->all([], 'name');
        $this->view('notes/create', ['title' => 'Add Note', 'students' => $students, 'classes' => $classes]);
    }

    public function store(): void
    {
        $this->authorize('notes', 'add');
        $this->validateCsrf();
        $data = Request::only(['student_id','class_id','title','content','note_type','visibility']);
        $v = new Validator($data);
        $v->required('title')->required('content');
        if ($v->fails()) redirect_with_error('/notes/create', $v->firstError());

        $data['student_id'] = $data['student_id'] ?: null;
        $data['class_id'] = $data['class_id'] ?: null;
        (new Note())->create($data + ['created_by' => Auth::id()]);
        log_activity('create', 'notes', "Added note: {$data['title']}");
        redirect_with_success('/notes', 'Note added successfully.');
    }

    public function edit(int $id): void
    {
        $this->authorize('notes', 'edit');
        $note = (new Note())->find($id);
        if (!$note) Response::redirect('/notes');
        $students = Database::fetchAll(
            "SELECT u.* FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='student' AND u.tuition_center_id=:c",
            ['c' => Auth::centerId()]
        );
        $classes = (new ClassRoom())->all([], 'name');
        $this->view('notes/edit', ['title' => 'Edit Note', 'note' => $note, 'students' => $students, 'classes' => $classes]);
    }

    public function update(int $id): void
    {
        $this->authorize('notes', 'edit');
        $this->validateCsrf();
        $data = Request::only(['student_id','class_id','title','content','note_type','visibility']);
        $data['student_id'] = $data['student_id'] ?: null;
        $data['class_id'] = $data['class_id'] ?: null;
        (new Note())->update($id, $data);
        redirect_with_success('/notes', 'Note updated.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('notes', 'delete');
        $this->validateCsrf();
        (new Note())->delete($id);
        redirect_with_success('/notes', 'Note deleted.');
    }
}
