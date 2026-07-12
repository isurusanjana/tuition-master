<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        $center = Auth::centerId();
        $role = Auth::roleSlug();
        $stats = [];

        if (Auth::isSuperAdmin()) {
            $stats['centers'] = Database::fetchOne("SELECT COUNT(*) c FROM tuition_centers")['c'];
            $stats['users'] = Database::fetchOne("SELECT COUNT(*) c FROM users")['c'];
            $stats['classes'] = Database::fetchOne("SELECT COUNT(*) c FROM classes")['c'];
            $stats['active_centers'] = Database::fetchOne("SELECT COUNT(*) c FROM tuition_centers WHERE status='active'")['c'];
        } elseif (in_array($role, ['center_admin', 'admin_staff'])) {
            $stats['students'] = Database::fetchOne("SELECT COUNT(*) c FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='student' AND u.tuition_center_id=:c", ['c' => $center])['c'];
            $stats['teachers'] = Database::fetchOne("SELECT COUNT(*) c FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='teacher' AND u.tuition_center_id=:c", ['c' => $center])['c'];
            $stats['classes'] = Database::fetchOne("SELECT COUNT(*) c FROM classes WHERE tuition_center_id=:c", ['c' => $center])['c'];
            $stats['exams'] = Database::fetchOne("SELECT COUNT(*) c FROM exams WHERE tuition_center_id=:c", ['c' => $center])['c'];
        } elseif ($role === 'teacher') {
            $classModel = new ClassRoom();
            $myClasses = $classModel->classesForTeacher(Auth::id());
            $stats['classes'] = count($myClasses);
            $classIds = array_column($myClasses, 'id');
            $stats['students'] = $classIds ? Database::fetchOne(
                "SELECT COUNT(DISTINCT student_id) c FROM class_student WHERE class_id IN (" . implode(',', array_map('intval', $classIds)) . ")"
            )['c'] : 0;
            $stats['exams'] = $classIds ? Database::fetchOne(
                "SELECT COUNT(*) c FROM exams WHERE class_id IN (" . implode(',', array_map('intval', $classIds)) . ")"
            )['c'] : 0;
        } elseif ($role === 'student') {
            $attModel = new Attendance();
            $stats['attendance'] = $attModel->studentSummary(Auth::id());
            $markModel = new Mark();
            $stats['recent_marks'] = array_slice($markModel->forStudent(Auth::id()), 0, 5);
        } elseif ($role === 'parent') {
            $children = Database::fetchAll(
                "SELECT u.* FROM users u JOIN student_parent sp ON sp.student_id = u.id WHERE sp.parent_id = :p",
                ['p' => Auth::id()]
            );
            $stats['children'] = $children;
        }

        $notifModel = new Notification();
        $notifications = $notifModel->forCurrentUser(5);

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'notifications' => $notifications,
        ]);
    }
}
