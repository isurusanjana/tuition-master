<?php
class HelpArticle extends Model
{
    protected string $table = 'help_articles';
    protected bool $tenantScoped = false;
    protected array $fillable = ['module_key','title','content','video_url','role_visibility','sort_order'];

    public function forModule(string $moduleKey): array
    {
        return Database::fetchAll("SELECT * FROM help_articles WHERE module_key = :m ORDER BY sort_order", ['m' => $moduleKey]);
    }
}
