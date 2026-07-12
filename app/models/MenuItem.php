<?php
class MenuItem extends Model
{
    protected string $table = 'menu_items';
    protected bool $tenantScoped = false;
    protected array $fillable = ['parent_id','label','icon','route','module_key','sort_order','is_active'];
}
