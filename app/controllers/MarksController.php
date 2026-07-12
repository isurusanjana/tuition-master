<?php
class MarksController extends Controller
{
    public function index(): void
    {
        $this->authorize('marks', 'view');
        $examModel = new Exam();
        $classModel = new ClassRoom();
        if (Auth::roleSlug() === 'teacher') {
            $classIds = array_column($classModel->classesForTeacher(Auth::id()), 'id');
            $exams = $examModel->withClass($classIds);
        } elseif (Auth::roleSlug() === 'student') {
            $marks = (new Mark())->forStudent(Auth::id());
            $this->view('marks/student', ['title' => 'My Marks', 'marks' => $marks]);
            return;
        } else {
            $exams = $examModel->withClass();
        }
        $this->view('marks/index', ['title' => 'Marks', 'exams' => $exams]);
    }

    public function byExam(int $examId): void
    {
        $this->authorize('marks', 'view');
        $exam = (new Exam())->find($examId);
        if (!$exam) Response::redirect('/marks');
        $students = (new ClassRoom())->students($exam['class_id']);
        $assignedIds = (new Exam())->assignedStudentIds($examId);
        if (!empty($assignedIds)) {
            $students = array_filter($students, fn($s) => in_array((int) $s['id'], $assignedIds));
        }
        $marksRows = (new Mark())->forExam($examId);
        $marksByStudent = [];
        foreach ($marksRows as $m) $marksByStudent[$m['student_id']] = $m;

        $this->view('marks/by_exam', [
            'title' => 'Marks - ' . $exam['title'], 'exam' => $exam, 'students' => $students, 'marksByStudent' => $marksByStudent,
        ]);
    }

    public function save(int $examId): void
    {
        $this->authorize('marks', 'add');
        $this->validateCsrf();
        $exam = (new Exam())->find($examId);
        if (!$exam) Response::redirect('/marks');

        $marksInput = Request::input('marks', []);   // [student_id => marks]
        $gradeInput = Request::input('grade', []);
        $remarksInput = Request::input('remarks', []);

        $markModel = new Mark();
        foreach ((array) $marksInput as $studentId => $value) {
            if ($value === '') continue;
            $markModel->upsert($examId, (int) $studentId, (float) $value, $gradeInput[$studentId] ?? '', $remarksInput[$studentId] ?? '', Auth::id());
        }
        log_activity('update', 'marks', "Saved marks for exam #$examId");
        redirect_with_success("/marks/exam/$examId", 'Marks saved successfully.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('marks', 'delete');
        $this->validateCsrf();
        (new Mark())->delete($id);
        Response::back('/marks');
    }
}
