<h4 class="mb-3">Edit Role</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('roles.update', ['id' => $role['id']]) ?>">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Role Name</label><input name="name" class="form-control" value="<?= e($role['name']) ?>" required></div>
    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= e($role['description']) ?></textarea></div>
    <button class="btn btn-tm text-white">Save</button>
</form>
</div>
