<?php
class HelpController extends Controller
{
    public function index(): void
    {
        $module = Request::input('module', '');
        $model = new HelpArticle();
        $articles = $module ? $model->forModule($module) : Database::fetchAll("SELECT * FROM help_articles ORDER BY module_key, sort_order");
        $modules = Database::fetchAll("SELECT DISTINCT module_key FROM help_articles ORDER BY module_key");
        $this->view('help/index', ['title' => 'Help & Tutorials', 'articles' => $articles, 'modules' => $modules, 'activeModule' => $module]);
    }
}
