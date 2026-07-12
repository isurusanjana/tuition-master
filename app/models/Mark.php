<?php
class Mark extends Model
{
    protected string $table = 'marks';
    protected bool $tenantScoped = false;
    protected array $fillable = ['exam_id','student_id','marks_obtained','grade','remarks','recorded_by'];

    public function forExam(int $examId): array
    {
        return Database::fetchAll(
            "SELECT m.*, u.first_name, u.last_name FROM marks m JOIN users u ON u.id = m.student_id WHERE m.exam_id = :e",
            ['e' => $examId]
        );
    }

    public function upsert(int $examId, int $studentId, float $marksObtained, string $grade, string $remarks, int $recordedBy): void
    {
        Database::query(
            "INSERT INTO marks (exam_id, student_id, marks_obtained, grade, remarks, recorded_by)
             VALUES (:e,:s,:m,:g,:r,:rb)
             ON DUPLICATE KEY UPDATE marks_obtained=VALUES(marks_obtained), grade=VALUES(grade), remarks=VALUES(remarks), recorded_by=VALUES(recorded_by)",
            ['e' => $examId, 's' => $studentId, 'm' => $marksObtained, 'g' => $grade, 'r' => $remarks, 'rb' => $recordedBy]
        );
    }

    public function forStudent(int $studentId): array
    {
        return Database::fetchAll(
            "SELECT mk.*, e.title as exam_title, e.total_marks, e.exam_date FROM marks mk
             JOIN exams e ON e.id = mk.exam_id WHERE mk.student_id = :s ORDER BY e.exam_date DESC",
            ['s' => $studentId]
        );
    }
}
