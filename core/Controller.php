<?php
abstract class Controller
{
    protected bool $requiresAuth = true;

    public function __construct()
    {
        if ($this->requiresAuth && !Auth::check()) {
            Response::redirect('/login');
        }
    }

    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function authorize(string $module, string $action): void
    {
        Permission::authorize($module, $action);
    }

    protected function validateCsrf(): void
    {
        if (!Request::csrfCheck()) {
            redirect_with_error('/', 'Invalid or expired form submission. Please try again.');
        }
    }

    /** Guard: ensures $targetUser belongs to caller's manageable hierarchy */
    protected function guardUser(?array $targetUser): void
    {
        if (!$targetUser || !Auth::canManageUser($targetUser)) {
            http_response_code(403);
            View::render('errors/403', ['module' => 'users', 'action' => 'view']);
            exit;
        }
    }
}
