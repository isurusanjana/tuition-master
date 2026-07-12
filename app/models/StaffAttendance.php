<?php
class StaffAttendance extends Model
{
    protected string $table = 'staff_attendance';
    protected array $fillable = ['tuition_center_id','staff_id','marked_by','attendance_date','status','remarks'];

    public function upsert(int $staffId, string $date, string $status, string $remarks, int $markedBy): void
    {
        Database::query(
            "INSERT INTO staff_attendance (tuition_center_id, staff_id, marked_by, attendance_date, status, remarks)
             VALUES (:tc,:s,:m,:d,:st,:r)
             ON DUPLICATE KEY UPDATE status=VALUES(status), remarks=VALUES(remarks), marked_by=VALUES(marked_by)",
            ['tc' => Auth::centerId(), 's' => $staffId, 'm' => $markedBy, 'd' => $date, 'st' => $status, 'r' => $remarks]
        );
    }

    public function forDate(string $date): array
    {
        return Database::fetchAll(
            "SELECT sa.*, u.first_name, u.last_name FROM staff_attendance sa
             JOIN users u ON u.id = sa.staff_id WHERE sa.attendance_date = :d AND sa.tuition_center_id = :c",
            ['d' => $date, 'c' => Auth::centerId()]
        );
    }
}
