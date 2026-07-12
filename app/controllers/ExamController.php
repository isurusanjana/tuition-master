<?php
class ExamController extends Controller
{
    public function index(): void
    {
        $this->authorize('exams', 'view');
        $classModel = new ClassRoom();
        $examModel = new Exam();

        if (Auth::roleSlug() === 'teacher') {
            $classIds = array_column($classModel->classesForTeacher(Auth::id()), 'id');
            $exams = $examModel->withClass($classIds);
        } elseif (Auth::roleSlug() === 'student') {
            $exams = $examModel->forStudent(Auth::id());
        } else {
            $exams = $examModel->withClass();
        }

        $this->view('exams/index', ['title' => 'Exams', 'exams' => $exams]);
    }

    public function create(): void
    {
        $this->authorize('exams', 'add');
        $classModel = new ClassRoom();
        $classes = Auth::roleSlug() === 'teacher' ? $classModel->classesForTeacher(Auth::id()) : $classModel->all([], 'name');
        $this->view('exams/create', ['title' => 'Add Exam', 'classes' => $classes]);
    }

    public function store(): void
    {
        $this->authorize('exams', 'add');
        $this->validateCsrf();
        $data = Request::only(['class_id','title','description','exam_date','total_marks','pass_marks']);
        $v = new Validator($data);
        $v->required('class_id')->required('title')->required('exam_date')->numeric('total_marks')->numeric('pass_marks');
        if ($v->fails()) redirect_with_error('/exams/create', $v->firstError());

        $id = (new Exam())->create($data + ['created_by' => Auth::id(), 'status' => 'published']);
        log_activity('create', 'exams', "Created exam: {$data['title']}");
        redirect_with_success("/exams/$id", 'Exam created successfully.');
    }

    public function show(int $id): void
    {
        $this->authorize('exams', 'view');
        $exam = (new Exam())->find($id);
        if (!$exam) Response::redirect('/exams');
        $class = (new ClassRoom())->find($exam['class_id']);
        $students = (new ClassRoom())->students($exam['class_id']);
        $assignedIds = (new Exam())->assignedStudentIds($id);
        $marks = (new Mark())->forExam($id);
        $this->view('exams/show', [
            'title' => $exam['title'], 'exam' => $exam, 'class' => $class, 'students' => $students,
            'assignedIds' => $assignedIds, 'marks' => $marks,
        ]);
    }

    public function edit(int $id): void
    {
        $this->authorize('exams', 'edit');
        $exam = (new Exam())->find($id);
        if (!$exam) Response::redirect('/exams');
        $classes = (new ClassRoom())->all([], 'name');
        $this->view('exams/edit', ['title' => 'Edit Exam', 'exam' => $exam, 'classes' => $classes]);
    }

    public function update(int $id): void
    {
        $this->authorize('exams', 'edit');
        $this->validateCsrf();
        $data = Request::only(['class_id','title','description','exam_date','total_marks','pass_marks','status']);
        (new Exam())->update($id, $data);
        log_activity('update', 'exams', "Updated exam #$id");
        redirect_with_success("/exams/$id", 'Exam updated.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('exams', 'delete');
        $this->validateCsrf();
        (new Exam())->delete($id);
        log_activity('delete', 'exams', "Deleted exam #$id");
        redirect_with_success('/exams', 'Exam deleted.');
    }

    /** assign exam only to selected students (within the logged-in user's manageable hierarchy) */
    public function assign(int $id): void
    {
        $this->authorize('exams', 'assign');
        $this->validateCsrf();
        $studentIds = Request::input('student_ids', []);
        (new Exam())->assignStudents($id, array_map('intval', (array) $studentIds), Auth::id());
        log_activity('assign', 'exams', "Assigned exam #$id to " . count($studentIds) . " student(s)");
        redirect_with_success("/exams/$id", 'Exam assignment updated.');
    }
}
