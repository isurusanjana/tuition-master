<?php
class AttendanceController extends Controller
{
    public function index(): void
    {
        $this->authorize('attendance', 'view');
        $classModel = new ClassRoom();
        $classes = Auth::roleSlug() === 'teacher' ? $classModel->classesForTeacher(Auth::id()) : $classModel->all([], 'name');

        $classId = (int) Request::input('class_id', $classes[0]['id'] ?? 0);
        $date = Request::input('date', date('Y-m-d'));

        $records = [];
        if ($classId) {
            $attModel = new Attendance();
            $records = $attModel->forClassDate($classId, $date);
        }

        $this->view('attendance/index', [
            'title' => 'Attendance', 'classes' => $classes, 'records' => $records,
            'classId' => $classId, 'date' => $date,
        ]);
    }

    public function markForm(): void
    {
        $this->authorize('attendance', 'mark');
        $classModel = new ClassRoom();
        $classes = Auth::roleSlug() === 'teacher' ? $classModel->classesForTeacher(Auth::id()) : $classModel->all([], 'name');
        $classId = (int) Request::input('class_id', $classes[0]['id'] ?? 0);
        $date = Request::input('date', date('Y-m-d'));

        $students = $classId ? $classModel->students($classId) : [];
        $existing = $classId ? $classModel->find($classId) : null;
        $attModel = new Attendance();
        $existingRecords = [];
        if ($classId) {
            foreach ($attModel->forClassDate($classId, $date) as $r) {
                $existingRecords[$r['student_id']] = $r;
            }
        }

        $this->view('attendance/mark', [
            'title' => 'Mark Attendance', 'classes' => $classes, 'students' => $students,
            'classId' => $classId, 'date' => $date, 'existingRecords' => $existingRecords,
        ]);
    }

    public function mark(): void
    {
        $this->authorize('attendance', 'mark');
        $this->validateCsrf();
        $classId = (int) Request::input('class_id');
        $date = Request::input('date');
        $statuses = Request::input('status', []);   // [student_id => status]
        $remarks = Request::input('remarks', []);   // [student_id => remark]

        if (!$classId || !$date) redirect_with_error('/attendance/mark', 'Please select a class and date.');

        $attModel = new Attendance();
        foreach ((array) $statuses as $studentId => $status) {
            $attModel->upsert($classId, (int) $studentId, $date, $status, $remarks[$studentId] ?? '', Auth::id());
        }
        log_activity('mark', 'attendance', "Marked attendance for class #$classId on $date");
        redirect_with_success("/attendance/mark?class_id=$classId&date=$date", 'Attendance saved successfully.');
    }

    public function staffIndex(): void
    {
        $this->authorize('attendance', 'view');
        $date = Request::input('date', date('Y-m-d'));
        $staffModel = new StaffAttendance();
        $records = $staffModel->forDate($date);
        $recordedIds = array_column($records, 'staff_id');
        $allStaff = Database::fetchAll(
            "SELECT u.* FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug IN ('teacher','admin_staff') AND u.tuition_center_id=:c",
            ['c' => Auth::centerId()]
        );
        $this->view('attendance/staff', ['title' => 'Staff Attendance', 'date' => $date, 'records' => $records, 'staff' => $allStaff, 'recordedIds' => $recordedIds]);
    }

    public function markStaff(): void
    {
        $this->authorize('attendance', 'mark');
        $this->validateCsrf();
        $date = Request::input('date');
        $statuses = Request::input('status', []);
        $remarks = Request::input('remarks', []);
        $model = new StaffAttendance();
        foreach ((array) $statuses as $staffId => $status) {
            $model->upsert((int) $staffId, $date, $status, $remarks[$staffId] ?? '', Auth::id());
        }
        redirect_with_success("/attendance/staff?date=$date", 'Staff attendance saved.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('attendance', 'delete');
        $this->validateCsrf();
        (new Attendance())->delete($id);
        Response::back('/attendance');
    }
}
