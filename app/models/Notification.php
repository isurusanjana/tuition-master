<?php
class Notification extends Model
{
    protected string $table = 'notifications';
    protected array $fillable = ['tuition_center_id','title','message','type','target_role_id','created_by','starts_at','ends_at'];

    /** notifications visible to the current logged-in user (their center OR system-wide broadcasts) */
    public function forCurrentUser(int $limit = 10): array
    {
        $centerId = Auth::centerId();
        $roleId = Auth::user()['role_id'] ?? null;
        $sql = "SELECT n.*, (nr.id IS NOT NULL) as is_read FROM notifications n
                LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id = :uid
                WHERE (n.tuition_center_id = :cid OR n.tuition_center_id IS NULL)
                AND (n.target_role_id IS NULL OR n.target_role_id = :rid)
                AND (n.ends_at IS NULL OR n.ends_at >= NOW())
                ORDER BY n.created_at DESC LIMIT $limit";
        return Database::fetchAll($sql, ['uid' => Auth::id(), 'cid' => $centerId, 'rid' => $roleId]);
    }

    public function markRead(int $notificationId, int $userId): void
    {
        Database::query("INSERT IGNORE INTO notification_reads (notification_id, user_id) VALUES (:n,:u)", ['n' => $notificationId, 'u' => $userId]);
    }
}
