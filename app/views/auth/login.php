<form method="POST" action="<?= route('login') ?>">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Username or Email</label>
        <input type="text" name="username" class="form-control form-control-lg" required autofocus>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control form-control-lg" required>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= route('forgot') ?>" class="small">Forgot password?</a>
    </div>
    <button type="submit" class="btn btn-tm btn-lg w-100 text-white">Sign In</button>
    <p class="text-center text-muted small mt-4 mb-0">Default: superadmin / Admin@123 (change after first login)</p>
</form>
