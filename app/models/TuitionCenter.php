<?php
class TuitionCenter extends Model
{
    protected string $table = 'tuition_centers';
    protected bool $tenantScoped = false; // super-admin-only resource; not scoped by itself
    protected array $fillable = ['name','code','email','phone','address','logo','status','created_by'];
}
