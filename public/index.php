<?php
require_once __DIR__ . '/../config/config.php';

// ---- simple autoloader for core classes and models ----
spl_autoload_register(function ($class) {
    $paths = [
        CORE_PATH . "/$class.php",
        APP_PATH . "/models/$class.php",
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

require_once CORE_PATH . '/Helpers.php';

Session::start();

global $router;
$router = new Router();

// ---------------- AUTH ----------------
$router->get('/login', 'AuthController@showLogin', 'login');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout', 'logout');
$router->get('/forgot-password', 'AuthController@showForgot', 'forgot');
$router->post('/forgot-password', 'AuthController@forgot');

// ---------------- DASHBOARD ----------------
$router->get('/', 'DashboardController@index', 'dashboard.index');
$router->get('/dashboard', 'DashboardController@index');

// ---------------- TUITION CENTERS (Super Admin only) ----------------
$router->get('/tuition-centers', 'TuitionCenterController@index', 'tuition_centers.index');
$router->get('/tuition-centers/create', 'TuitionCenterController@create', 'tuition_centers.create');
$router->post('/tuition-centers', 'TuitionCenterController@store', 'tuition_centers.store');
$router->get('/tuition-centers/{id}/edit', 'TuitionCenterController@edit', 'tuition_centers.edit');
$router->post('/tuition-centers/{id}', 'TuitionCenterController@update', 'tuition_centers.update');
$router->post('/tuition-centers/{id}/delete', 'TuitionCenterController@destroy', 'tuition_centers.delete');
$router->get('/tuition-centers/{id}', 'TuitionCenterController@show', 'tuition_centers.show');

// ---------------- USERS ----------------
$router->get('/users', 'UserController@index', 'users.index');
$router->get('/users/create', 'UserController@create', 'users.create');
$router->post('/users', 'UserController@store', 'users.store');
$router->get('/users/{id}', 'UserController@show', 'users.show');
$router->get('/users/{id}/edit', 'UserController@edit', 'users.edit');
$router->post('/users/{id}', 'UserController@update', 'users.update');
$router->post('/users/{id}/delete', 'UserController@destroy', 'users.delete');
$router->get('/users/{id}/permissions', 'UserController@permissions', 'users.permissions');
$router->post('/users/{id}/permissions', 'UserController@savePermissions', 'users.permissions.save');

// ---------------- ROLES ----------------
$router->get('/roles', 'RoleController@index', 'roles.index');
$router->get('/roles/create', 'RoleController@create', 'roles.create');
$router->post('/roles', 'RoleController@store', 'roles.store');
$router->get('/roles/{id}/edit', 'RoleController@edit', 'roles.edit');
$router->post('/roles/{id}', 'RoleController@update', 'roles.update');
$router->post('/roles/{id}/delete', 'RoleController@destroy', 'roles.delete');
$router->get('/roles/{id}/access', 'RoleController@access', 'roles.access');
$router->post('/roles/{id}/access', 'RoleController@saveAccess', 'roles.access.save');

// ---------------- CLASSES ----------------
$router->get('/classes', 'ClassController@index', 'classes.index');
$router->get('/classes/create', 'ClassController@create', 'classes.create');
$router->post('/classes', 'ClassController@store', 'classes.store');
$router->get('/classes/{id}', 'ClassController@show', 'classes.show');
$router->get('/classes/{id}/edit', 'ClassController@edit', 'classes.edit');
$router->post('/classes/{id}', 'ClassController@update', 'classes.update');
$router->post('/classes/{id}/delete', 'ClassController@destroy', 'classes.delete');
$router->post('/classes/{id}/assign-teacher', 'ClassController@assignTeacher', 'classes.assign_teacher');
$router->post('/classes/{id}/assign-student', 'ClassController@assignStudent', 'classes.assign_student');
$router->post('/classes/{id}/remove-teacher/{tid}', 'ClassController@removeTeacher', 'classes.remove_teacher');
$router->post('/classes/{id}/remove-student/{sid}', 'ClassController@removeStudent', 'classes.remove_student');

// ---------------- ATTENDANCE ----------------
$router->get('/attendance', 'AttendanceController@index', 'attendance.index');
$router->get('/attendance/mark', 'AttendanceController@markForm', 'attendance.mark_form');
$router->post('/attendance/mark', 'AttendanceController@mark', 'attendance.mark');
$router->get('/attendance/staff', 'AttendanceController@staffIndex', 'attendance.staff_index');
$router->post('/attendance/staff', 'AttendanceController@markStaff', 'attendance.mark_staff');
$router->post('/attendance/{id}/delete', 'AttendanceController@destroy', 'attendance.delete');

// ---------------- EXAMS ----------------
$router->get('/exams', 'ExamController@index', 'exams.index');
$router->get('/exams/create', 'ExamController@create', 'exams.create');
$router->post('/exams', 'ExamController@store', 'exams.store');
$router->get('/exams/{id}', 'ExamController@show', 'exams.show');
$router->get('/exams/{id}/edit', 'ExamController@edit', 'exams.edit');
$router->post('/exams/{id}', 'ExamController@update', 'exams.update');
$router->post('/exams/{id}/delete', 'ExamController@destroy', 'exams.delete');
$router->post('/exams/{id}/assign', 'ExamController@assign', 'exams.assign');

// ---------------- MARKS ----------------
$router->get('/marks', 'MarksController@index', 'marks.index');
$router->get('/marks/exam/{examId}', 'MarksController@byExam', 'marks.by_exam');
$router->post('/marks/exam/{examId}', 'MarksController@save', 'marks.save');
$router->post('/marks/{id}/delete', 'MarksController@destroy', 'marks.delete');

// ---------------- NOTES ----------------
$router->get('/notes', 'NoteController@index', 'notes.index');
$router->get('/notes/create', 'NoteController@create', 'notes.create');
$router->post('/notes', 'NoteController@store', 'notes.store');
$router->get('/notes/{id}/edit', 'NoteController@edit', 'notes.edit');
$router->post('/notes/{id}', 'NoteController@update', 'notes.update');
$router->post('/notes/{id}/delete', 'NoteController@destroy', 'notes.delete');

// ---------------- LESSONS ----------------
$router->get('/lessons', 'LessonController@index', 'lessons.index');
$router->get('/lessons/create', 'LessonController@create', 'lessons.create');
$router->post('/lessons', 'LessonController@store', 'lessons.store');
$router->get('/lessons/{id}', 'LessonController@show', 'lessons.show');
$router->get('/lessons/{id}/edit', 'LessonController@edit', 'lessons.edit');
$router->post('/lessons/{id}', 'LessonController@update', 'lessons.update');
$router->post('/lessons/{id}/delete', 'LessonController@destroy', 'lessons.delete');
$router->get('/lessons/{id}/assign', 'LessonController@assignForm', 'lessons.assign_form');
$router->post('/lessons/{id}/assign', 'LessonController@assign', 'lessons.assign');

// ---------------- REPORTS ----------------
$router->get('/reports', 'ReportController@index', 'reports.index');
$router->get('/reports/student/{id}', 'ReportController@studentSummary', 'reports.student');

// ---------------- PAYROLL ----------------
$router->get('/payroll', 'PayrollController@index', 'payroll.index');
$router->get('/payroll/create', 'PayrollController@create', 'payroll.create');
$router->post('/payroll', 'PayrollController@store', 'payroll.store');
$router->get('/payroll/{id}/edit', 'PayrollController@edit', 'payroll.edit');
$router->post('/payroll/{id}', 'PayrollController@update', 'payroll.update');
$router->post('/payroll/{id}/delete', 'PayrollController@destroy', 'payroll.delete');
$router->post('/payroll/{id}/mark-paid', 'PayrollController@markPaid', 'payroll.mark_paid');

// ---------------- INVENTORY ----------------
$router->get('/inventory', 'InventoryController@index', 'inventory.index');
$router->get('/inventory/create', 'InventoryController@create', 'inventory.create');
$router->post('/inventory', 'InventoryController@store', 'inventory.store');
$router->get('/inventory/{id}/edit', 'InventoryController@edit', 'inventory.edit');
$router->post('/inventory/{id}', 'InventoryController@update', 'inventory.update');
$router->post('/inventory/{id}/delete', 'InventoryController@destroy', 'inventory.delete');
$router->post('/inventory/{id}/transaction', 'InventoryController@transaction', 'inventory.transaction');

// ---------------- NOTIFICATIONS ----------------
$router->get('/notifications', 'NotificationController@index', 'notifications.index');
$router->get('/notifications/create', 'NotificationController@create', 'notifications.create');
$router->post('/notifications', 'NotificationController@store', 'notifications.store');
$router->post('/notifications/{id}/delete', 'NotificationController@destroy', 'notifications.delete');
$router->post('/notifications/{id}/read', 'NotificationController@markRead', 'notifications.read');

// ---------------- THEME ----------------
$router->get('/theme', 'ThemeController@index', 'theme.index');
$router->post('/theme', 'ThemeController@update', 'theme.update');

// ---------------- HELP ----------------
$router->get('/help', 'HelpController@index', 'help.index');

// ---------------- PROFILE ----------------
$router->get('/profile', 'ProfileController@index', 'profile.index');
$router->post('/profile', 'ProfileController@update', 'profile.update');
$router->post('/profile/password', 'ProfileController@changePassword', 'profile.password');

try {
    $router->dispatch(Request::method(), $_SERVER['REQUEST_URI']);
} catch (RuntimeException $e) {
    http_response_code(500);
    if (APP_DEBUG) {
        echo '<h1>Application Error</h1><pre>' . e($e->getMessage()) . '</pre>';
    } else {
        echo '<h1>Something went wrong</h1><p>Please try again later or contact your administrator.</p>';
    }
}
