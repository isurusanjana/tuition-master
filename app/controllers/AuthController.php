<?php
class AuthController extends Controller
{
    protected bool $requiresAuth = false;

    public function showLogin(): void
    {
        if (Auth::check()) Response::redirect('/dashboard');
        View::render('auth/login', ['title' => 'Login'], 'layouts/blank');
    }

    public function login(): void
    {
        if (!Request::csrfCheck()) {
            redirect_with_error('/login', 'Session expired, please try again.');
        }
        $username = Request::input('username');
        $password = Request::input('password');

        $v = new Validator(['username' => $username, 'password' => $password]);
        $v->required('username')->required('password');
        if ($v->fails()) {
            redirect_with_error('/login', $v->firstError());
        }

        if (Auth::attempt($username, $password)) {
            log_activity('login', 'auth', 'User logged in');
            Response::redirect('/dashboard');
        }

        redirect_with_error('/login', 'Invalid username/email or password.');
    }

    public function logout(): void
    {
        log_activity('logout', 'auth', 'User logged out');
        Auth::logout();
        Response::redirect('/login');
    }

    public function showForgot(): void
    {
        View::render('auth/forgot', ['title' => 'Forgot Password'], 'layouts/blank');
    }

    public function forgot(): void
    {
        // In production: send reset link via email. For this starter kit we just show a confirmation.
        Session::flash('success', 'If that account exists, password reset instructions have been sent.');
        Response::redirect('/login');
    }
}
