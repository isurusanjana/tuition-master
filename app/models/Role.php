<?php
class Role extends Model
{
    protected string $table = 'roles';
    protected bool $tenantScoped = false;
    protected array $fillable = ['tuition_center_id','name','slug','description','level','is_system'];

    /** roles visible/usable by the current user (global system roles + tenant custom roles), excluding higher-or-equal authority */
    public function assignableRoles(): array
    {
        $me = Auth::user();
        $myLevel = Auth::roleLevel();
        $sql = "SELECT * FROM roles WHERE (tuition_center_id IS NULL OR tuition_center_id = :cid)";
        $params = ['cid' => Auth::centerId()];
        if (!Auth::isSuperAdmin()) {
            $sql .= " AND level > :lvl AND slug != 'super_admin'";
            $params['lvl'] = $myLevel;
        }
        $sql .= " ORDER BY level ASC";
        return Database::fetchAll($sql, $params);
    }
}
