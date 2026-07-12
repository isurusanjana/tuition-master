<?php
class ProfileController extends Controller
{
    public function index(): void
    {
        $user = (new User())->findWithRole(Auth::id());
        $this->view('profile/index', ['title' => 'My Profile', 'user' => $user]);
    }

    public function update(): void
    {
        $this->validateCsrf();
        $data = Request::only(['first_name','last_name','phone','address','gender','dob']);
        $model = new User();
        $model->update(Auth::id(), $data);

        $photo = Request::file('photo');
        if ($photo) {
            $path = FileUpload::upload($photo, 'profiles', ['png','jpg','jpeg','webp'], 5);
            if ($path) $model->update(Auth::id(), ['photo' => $path]);
        }

        // refresh session copy
        $fresh = $model->findWithRole(Auth::id());
        Session::set('user', $fresh);

        redirect_with_success('/profile', 'Profile updated successfully.');
    }

    public function changePassword(): void
    {
        $this->validateCsrf();
        $current = Request::input('current_password');
        $new = Request::input('new_password');
        $confirm = Request::input('confirm_password');

        $user = Database::fetchOne("SELECT * FROM users WHERE id = :id", ['id' => Auth::id()]);
        if (!password_verify($current, $user['password'])) {
            redirect_with_error('/profile', 'Current password is incorrect.');
        }
        if (strlen($new) < 6) {
            redirect_with_error('/profile', 'New password must be at least 6 characters.');
        }
        if ($new !== $confirm) {
            redirect_with_error('/profile', 'New password and confirmation do not match.');
        }

        Database::query("UPDATE users SET password = :p WHERE id = :id", ['p' => password_hash($new, PASSWORD_BCRYPT), 'id' => Auth::id()]);
        log_activity('update', 'profile', 'Changed password');
        redirect_with_success('/profile', 'Password changed successfully.');
    }
}
