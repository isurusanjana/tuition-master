<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? APP_NAME) ?> | <?= e(APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= asset('css/custom.css') ?>" rel="stylesheet">
</head>
<body class="auth-body d-flex align-items-center justify-content-center">
<div class="auth-card shadow-lg">
    <div class="text-center mb-4">
        <i class="bi bi-mortarboard-fill display-4 text-primary"></i>
        <h3 class="mt-2 mb-0 fw-bold"><?= e(APP_NAME) ?></h3>
        <p class="text-muted small">Multi-Tenant Tuition Center Management</p>
    </div>
    <?= $content ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
