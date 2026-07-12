<?php
class ReportController extends Controller
{
    public function index(): void
    {
        $this->authorize('reports', 'view');
        $role = Auth::roleSlug();

        if ($role === 'student') {
            Response::redirect('/reports/student/' . Auth::id());
        }

        if ($role === 'parent') {
            $children = Database::fetchAll(
                "SELECT u.* FROM users u JOIN student_parent sp ON sp.student_id = u.id WHERE sp.parent_id = :p",
                ['p' => Auth::id()]
            );
            $this->view('reports/index', ['title' => 'Reports', 'students' => $children, 'roleContext' => 'parent']);
            return;
        }

        if ($role === 'teacher') {
            $classIds = array_column((new ClassRoom())->classesForTeacher(Auth::id()), 'id');
            $students = $classIds ? Database::fetchAll(
                "SELECT DISTINCT u.* FROM users u JOIN class_student cs ON cs.student_id = u.id
                 WHERE cs.class_id IN (" . implode(',', array_map('intval', $classIds)) . ")"
            ) : [];
            $this->view('reports/index', ['title' => 'Reports', 'students' => $students, 'roleContext' => 'teacher']);
            return;
        }

        // center_admin / admin_staff / super_admin: all students in scope
        $students = Database::fetchAll(
            "SELECT u.* FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='student'" .
            (Auth::centerId() ? " AND u.tuition_center_id = :c" : ""),
            Auth::centerId() ? ['c' => Auth::centerId()] : []
        );
        $this->view('reports/index', ['title' => 'Reports', 'students' => $students, 'roleContext' => 'admin']);
    }

    public function studentSummary(int $id): void
    {
        $this->authorize('reports', 'view');
        $student = (new User())->findWithRole($id);
        if (!$student) Response::redirect('/reports');

        // access control: student can only see self; parent only their linked children; staff must manage this user
        $role = Auth::roleSlug();
        if ($role === 'student' && (int) Auth::id() !== $id) {
            http_response_code(403); View::render('errors/403', ['module' => 'reports', 'action' => 'view']); exit;
        }
        if ($role === 'parent') {
            $link = Database::fetchOne("SELECT id FROM student_parent WHERE student_id = :s AND parent_id = :p", ['s' => $id, 'p' => Auth::id()]);
            if (!$link) { http_response_code(403); View::render('errors/403', ['module' => 'reports', 'action' => 'view']); exit; }
        }
        if (in_array($role, ['center_admin','admin_staff','teacher']) && !Auth::isSuperAdmin()) {
            if ((int) $student['tuition_center_id'] !== (int) Auth::centerId()) {
                http_response_code(403); View::render('errors/403', ['module' => 'reports', 'action' => 'view']); exit;
            }
        }

        $attendance = (new Attendance())->studentSummary($id);
        $marks = (new Mark())->forStudent($id);
        $notes = (new Note())->all(['student_id' => $id], 'created_at DESC');
        $classes = (new ClassRoom())->classesForStudent($id);
        $lessons = (new Lesson())->forUser($id);

        $this->view('reports/student', [
            'title' => 'Student Summary - ' . $student['first_name'], 'student' => $student, 'attendance' => $attendance,
            'marks' => $marks, 'notes' => $notes, 'classes' => $classes, 'lessons' => $lessons,
        ]);
    }
}
