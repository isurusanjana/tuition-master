<?php
class Attendance extends Model
{
    protected string $table = 'attendance';
    protected array $fillable = ['tuition_center_id','class_id','student_id','marked_by','attendance_date','status','remarks'];

    public function forClassDate(int $classId, string $date): array
    {
        return Database::fetchAll(
            "SELECT a.*, u.first_name, u.last_name FROM attendance a
             JOIN users u ON u.id = a.student_id
             WHERE a.class_id = :c AND a.attendance_date = :d", ['c' => $classId, 'd' => $date]
        );
    }

    public function upsert(int $classId, int $studentId, string $date, string $status, string $remarks, int $markedBy): void
    {
        Database::query(
            "INSERT INTO attendance (tuition_center_id, class_id, student_id, marked_by, attendance_date, status, remarks)
             VALUES (:tc,:c,:s,:m,:d,:st,:r)
             ON DUPLICATE KEY UPDATE status = VALUES(status), remarks = VALUES(remarks), marked_by = VALUES(marked_by)",
            ['tc' => Auth::centerId(), 'c' => $classId, 's' => $studentId, 'm' => $markedBy, 'd' => $date, 'st' => $status, 'r' => $remarks]
        );
    }

    public function studentSummary(int $studentId): array
    {
        return Database::fetchOne(
            "SELECT
                SUM(status='present') as present_count,
                SUM(status='absent') as absent_count,
                SUM(status='late') as late_count,
                COUNT(*) as total
             FROM attendance WHERE student_id = :s", ['s' => $studentId]
        ) ?? ['present_count' => 0, 'absent_count' => 0, 'late_count' => 0, 'total' => 0];
    }
}
