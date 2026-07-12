<h4 class="mb-3">Add Custom Role</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('roles.store') ?>">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Role Name *</label><input name="name" class="form-control" required placeholder="e.g. Accountant"></div>
    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
    <button class="btn btn-tm text-white">Create Role</button>
</form>
</div>
