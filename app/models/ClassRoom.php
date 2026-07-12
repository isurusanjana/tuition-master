<?php
class ClassRoom extends Model
{
    protected string $table = 'classes';
    protected array $fillable = ['tuition_center_id','name','grade','subject','description','schedule','capacity','status','created_by'];

    public function teachers(int $classId): array
    {
        return Database::fetchAll(
            "SELECT u.* FROM users u JOIN class_teacher ct ON ct.teacher_id = u.id WHERE ct.class_id = :id",
            ['id' => $classId]
        );
    }

    public function students(int $classId): array
    {
        return Database::fetchAll(
            "SELECT u.*, cs.enrolled_at FROM users u JOIN class_student cs ON cs.student_id = u.id WHERE cs.class_id = :id",
            ['id' => $classId]
        );
    }

    public function addTeacher(int $classId, int $teacherId): void
    {
        Database::query("INSERT IGNORE INTO class_teacher (class_id, teacher_id) VALUES (:c,:t)", ['c' => $classId, 't' => $teacherId]);
    }

    public function addStudent(int $classId, int $studentId): void
    {
        Database::query("INSERT IGNORE INTO class_student (class_id, student_id, enrolled_at) VALUES (:c,:s,CURDATE())", ['c' => $classId, 's' => $studentId]);
    }

    public function removeTeacher(int $classId, int $teacherId): void
    {
        Database::query("DELETE FROM class_teacher WHERE class_id = :c AND teacher_id = :t", ['c' => $classId, 't' => $teacherId]);
    }

    public function removeStudent(int $classId, int $studentId): void
    {
        Database::query("DELETE FROM class_student WHERE class_id = :c AND student_id = :s", ['c' => $classId, 's' => $studentId]);
    }

    /** classes a teacher is assigned to (used to scope attendance/exam access for teachers) */
    public function classesForTeacher(int $teacherId): array
    {
        return Database::fetchAll(
            "SELECT c.* FROM classes c JOIN class_teacher ct ON ct.class_id = c.id WHERE ct.teacher_id = :t",
            ['t' => $teacherId]
        );
    }

    public function classesForStudent(int $studentId): array
    {
        return Database::fetchAll(
            "SELECT c.* FROM classes c JOIN class_student cs ON cs.class_id = c.id WHERE cs.student_id = :s",
            ['s' => $studentId]
        );
    }
}
