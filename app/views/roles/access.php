<h4 class="mb-1">Configure Access - <?= e($role['name']) ?></h4>
<p class="text-muted">Set the default menu items and CRUD permissions granted to every user with this role. Individual users can still be fine-tuned from Users &rarr; Permissions.</p>
<form method="POST" action="<?= route('roles.access.save', ['id' => $role['id']]) ?>">
<?= csrf_field() ?>
<div class="row g-3">
<div class="col-md-6">
<div class="tm-card p-3">
    <h6>Menu Items</h6>
    <?php foreach ($menus as $m): ?>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="menu_ids[]" value="<?= $m['id'] ?>" id="menu<?= $m['id'] ?>" <?= in_array($m['id'], $roleMenuIds)?'checked':'' ?>>
            <label class="form-check-label" for="menu<?= $m['id'] ?>"><i class="bi <?= e($m['icon']) ?>"></i> <?= e($m['label']) ?></label>
        </div>
    <?php endforeach; ?>
</div>
</div>
<div class="col-md-6">
<div class="tm-card p-3">
    <h6>CRUD Permissions</h6>
    <?php $grouped = []; foreach ($permissions as $p) { $grouped[$p['module_key']][] = $p; } ?>
    <?php foreach ($grouped as $module => $perms): ?>
        <div class="mb-2">
            <strong class="small text-uppercase text-muted"><?= e($module) ?></strong><br>
            <?php foreach ($perms as $p): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="perm_ids[]" value="<?= $p['id'] ?>" id="perm<?= $p['id'] ?>" <?= in_array($p['id'], $rolePermIds)?'checked':'' ?>>
                    <label class="form-check-label" for="perm<?= $p['id'] ?>"><?= e(ucfirst($p['action'])) ?></label>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
</div>
</div>
<div class="mt-3"><button class="btn btn-tm text-white">Save Access</button></div>
</form>
