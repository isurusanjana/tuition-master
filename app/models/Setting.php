<?php
class Setting extends Model
{
    protected string $table = 'settings';
    protected bool $tenantScoped = false;
    protected array $fillable = ['tuition_center_id','setting_key','setting_value'];

    public function set(?int $centerId, string $key, string $value): void
    {
        Database::query(
            "INSERT INTO settings (tuition_center_id, setting_key, setting_value) VALUES (:c,:k,:v)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            ['c' => $centerId, 'k' => $key, 'v' => $value]
        );
    }

    public function allFor(?int $centerId): array
    {
        $sql = $centerId ? "SELECT * FROM settings WHERE tuition_center_id = :c" : "SELECT * FROM settings WHERE tuition_center_id IS NULL";
        $rows = Database::fetchAll($sql, $centerId ? ['c' => $centerId] : []);
        $out = [];
        foreach ($rows as $r) $out[$r['setting_key']] = $r['setting_value'];
        return $out;
    }
}
