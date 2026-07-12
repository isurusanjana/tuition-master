<div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height:60vh;">
    <i class="bi bi-shield-lock display-1 text-danger"></i>
    <h3 class="mt-3">Access Denied</h3>
    <p class="text-muted">You don't have permission to <?= e($action ?? 'access') ?> <?= e($module ?? 'this resource') ?>.</p>
    <a href="<?= url('/dashboard') ?>" class="btn btn-tm text-white">Back to Dashboard</a>
</div>
