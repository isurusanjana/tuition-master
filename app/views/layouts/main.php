<?php
$menus = Permission::menus();
$orientation = setting('menu_orientation', 'vertical'); // vertical | horizontal
$primary = setting('theme_primary_color', '#4f46e5');
$secondary = setting('theme_secondary_color', '#0ea5e9');
$headerColor = setting('theme_header_color', '#ffffff');
$sidebarColor = setting('theme_sidebar_color', '#1e1b4b');
$buttonColor = setting('theme_button_color', $primary);
$textColor = setting('theme_text_color', '#1f2937');
$footerText = setting('theme_footer_text', '© ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.');
$siteName = setting('site_name', APP_NAME);
$siteLogo = setting('site_logo', '');
$currentUser = Auth::user();
$unreadNotifications = (new Notification())->forCurrentUser(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? '') ?> | <?= e($siteName) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.11/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="<?= asset('css/custom.css') ?>" rel="stylesheet">
<style>
:root {
    --tm-primary: <?= e($primary) ?>;
    --tm-secondary: <?= e($secondary) ?>;
    --tm-header: <?= e($headerColor) ?>;
    --tm-sidebar: <?= e($sidebarColor) ?>;
    --tm-button: <?= e($buttonColor) ?>;
    --tm-text: <?= e($textColor) ?>;
}
</style>
</head>
<body class="tm-<?= $orientation ?>">
<div class="tm-wrapper tm-<?= $orientation ?>-layout">

<?php if ($orientation === 'vertical'): ?>
    <aside class="tm-sidebar" id="tmSidebar">
        <div class="tm-sidebar-brand">
            <?php if ($siteLogo): ?><img src="<?= asset('uploads/' . e($siteLogo)) ?>" alt="logo" class="tm-logo"><?php endif; ?>
            <span><?= e($siteName) ?></span>
        </div>
        <nav class="tm-menu">
            <?php View::partial('layouts/menu_items', ['menus' => $menus]); ?>
        </nav>
    </aside>
<?php endif; ?>

    <div class="tm-main">
        <header class="tm-topbar">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" id="tmSidebarToggle"><i class="bi bi-list"></i></button>
            <?php if ($orientation === 'horizontal'): ?>
                <div class="tm-topbar-brand">
                    <?php if ($siteLogo): ?><img src="<?= asset('uploads/' . e($siteLogo)) ?>" alt="logo" class="tm-logo-sm"><?php endif; ?>
                    <span class="fw-bold"><?= e($siteName) ?></span>
                </div>
            <?php endif; ?>
            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-light position-relative" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <?php if (count(array_filter($unreadNotifications, fn($n) => !$n['is_read']))): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">!</span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-2" style="width:320px;">
                        <h6 class="dropdown-header">Notifications</h6>
                        <?php if (empty($unreadNotifications)): ?>
                            <div class="text-muted small px-2 py-3 text-center">No notifications</div>
                        <?php endif; ?>
                        <?php foreach ($unreadNotifications as $n): ?>
                            <div class="dropdown-item small border-bottom py-2 alert-<?= e($n['type']) ?>-subtle">
                                <strong><?= e($n['title']) ?></strong><br>
                                <span class="text-muted"><?= e(mb_strimwidth($n['message'], 0, 80, '...')) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <a href="<?= route('notifications.index') ?>" class="dropdown-item text-center text-primary small mt-1">View all</a>
                    </div>
                </div>
                <a href="<?= route('help.index') ?>" class="btn btn-light" title="Help & Tutorials"><i class="bi bi-question-circle"></i></a>
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <span class="tm-avatar"><?= e(strtoupper(substr($currentUser['first_name'] ?? 'U', 0, 1))) ?></span>
                        <span class="d-none d-md-inline"><?= e($currentUser['first_name'] ?? '') ?></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <span class="dropdown-item-text small text-muted"><?= e($currentUser['role_name'] ?? '') ?></span>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= route('profile.index') ?>"><i class="bi bi-person me-2"></i>My Profile</a>
                        <a class="dropdown-item" href="<?= route('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <?php if ($orientation === 'horizontal'): ?>
            <nav class="tm-menu-horizontal">
                <?php View::partial('layouts/menu_items', ['menus' => $menus, 'horizontal' => true]); ?>
            </nav>
        <?php endif; ?>

        <main class="tm-content">
            <?php if ($msg = flash_message('success')): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($msg = flash_message('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($msg = flash_message('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <?= $content ?>
        </main>

        <footer class="tm-footer">
            <small><?= e($footerText) ?></small>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.11/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
