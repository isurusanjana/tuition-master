<?php
class Exam extends Model
{
    protected string $table = 'exams';
    protected array $fillable = ['tuition_center_id','class_id','title','description','exam_date','total_marks','pass_marks','created_by','status'];

    /** exams joined with class name, tenant-scoped, optionally filtered to a set of class ids */
    public function withClass(array $classIds = []): array
    {
        $sql = "SELECT e.*, c.name as class_name FROM exams e JOIN classes c ON c.id = e.class_id WHERE e.tuition_center_id = :cid";
        $params = ['cid' => Auth::centerId()];
        if (!empty($classIds)) {
            $in = [];
            foreach ($classIds as $i => $cid) { $ph = "cls$i"; $in[] = ":$ph"; $params[$ph] = $cid; }
            $sql .= " AND e.class_id IN (" . implode(',', $in) . ")";
        }
        $sql .= " ORDER BY e.exam_date DESC";
        return Database::fetchAll($sql, $params);
    }

    public function assignedStudentIds(int $examId): array
    {
        $rows = Database::fetchAll("SELECT student_id FROM exam_assignments WHERE exam_id = :e", ['e' => $examId]);
        return array_map(fn($r) => (int) $r['student_id'], $rows);
    }

    public function assignStudents(int $examId, array $studentIds, int $assignedBy): void
    {
        Database::query("DELETE FROM exam_assignments WHERE exam_id = :e", ['e' => $examId]);
        foreach ($studentIds as $sid) {
            Database::query(
                "INSERT INTO exam_assignments (exam_id, student_id, assigned_by) VALUES (:e,:s,:a)",
                ['e' => $examId, 's' => $sid, 'a' => $assignedBy]
            );
        }
    }

    /** exams visible to a student: either assigned specifically or via their class with no specific assignment list */
    public function forStudent(int $studentId): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT e.* FROM exams e
             JOIN classes c ON c.id = e.class_id
             JOIN class_student cs ON cs.class_id = c.id AND cs.student_id = :s1
             LEFT JOIN exam_assignments ea ON ea.exam_id = e.id
             WHERE (ea.student_id = :s2 OR ea.id IS NULL) AND e.status != 'draft'
             ORDER BY e.exam_date DESC",
            ['s1' => $studentId, 's2' => $studentId]
        );
    }
}
