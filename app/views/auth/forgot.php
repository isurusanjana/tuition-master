<form method="POST" action="<?= route('forgot') ?>">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Registered Email</label>
        <input type="email" name="email" class="form-control form-control-lg" required autofocus>
    </div>
    <button type="submit" class="btn btn-tm btn-lg w-100 text-white">Send Reset Instructions</button>
    <p class="text-center mt-4 mb-0"><a href="<?= route('login') ?>" class="small">&larr; Back to login</a></p>
</form>
