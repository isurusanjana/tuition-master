<?php
class Lesson extends Model
{
    protected string $table = 'lessons';
    protected array $fillable = ['tuition_center_id','class_id','title','description','resource_type','file_path','external_url','uploaded_by'];

    public function assign(int $lessonId, array $userIds, int $assignedBy): void
    {
        foreach ($userIds as $uid) {
            Database::query(
                "INSERT IGNORE INTO lesson_assignments (lesson_id, user_id, assigned_by) VALUES (:l,:u,:a)",
                ['l' => $lessonId, 'u' => $uid, 'a' => $assignedBy]
            );
        }
    }

    public function assignedUsers(int $lessonId): array
    {
        return Database::fetchAll(
            "SELECT u.*, la.is_viewed, la.assigned_at FROM users u
             JOIN lesson_assignments la ON la.user_id = u.id WHERE la.lesson_id = :l",
            ['l' => $lessonId]
        );
    }

    public function forUser(int $userId): array
    {
        return Database::fetchAll(
            "SELECT l.*, la.is_viewed FROM lessons l
             JOIN lesson_assignments la ON la.lesson_id = l.id
             WHERE la.user_id = :u ORDER BY l.created_at DESC",
            ['u' => $userId]
        );
    }
}
