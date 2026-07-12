<h4 class="mb-1">Permissions - <?= e($user['first_name']) ?></h4>
<p class="text-muted">Fine-tune menu visibility and CRUD access for this individual user. These overrides take precedence over their role's default access (<?= e($user['role_name']) ?>).</p>

<form method="POST" action="<?= route('users.permissions.save', ['id' => $user['id']]) ?>">
<?= csrf_field() ?>
<div class="row g-3">
<div class="col-md-6">
<div class="tm-card p-3">
    <h6>Menu Visibility</h6>
    <table class="table table-sm">
        <thead><tr><th>Menu</th><th class="text-center">Show</th><th class="text-center">Hide</th><th class="text-center">Role Default</th></tr></thead>
        <tbody>
        <?php foreach ($menus as $m): $state = $menuMap[$m['id']] ?? null; ?>
            <tr>
                <td><i class="bi <?= e($m['icon']) ?>"></i> <?= e($m['label']) ?></td>
                <td class="text-center"><input type="radio" name="menu_state[<?= $m['id'] ?>]" value="allow" onclick="document.getElementById('ma_<?= $m['id'] ?>').value=1;document.getElementById('md_<?= $m['id'] ?>').value='';" <?= $state===true?'checked':'' ?>></td>
                <td class="text-center"><input type="radio" name="menu_state[<?= $m['id'] ?>]" value="deny" onclick="document.getElementById('md_<?= $m['id'] ?>').value=1;document.getElementById('ma_<?= $m['id'] ?>').value='';" <?= $state===false?'checked':'' ?>></td>
                <td class="text-center"><input type="radio" name="menu_state[<?= $m['id'] ?>]" value="default" onclick="document.getElementById('ma_<?= $m['id'] ?>').value='';document.getElementById('md_<?= $m['id'] ?>').value='';" <?= $state===null?'checked':'' ?>></td>
                <input type="hidden" id="ma_<?= $m['id'] ?>" name="menu_allowed[<?= $m['id'] ?>]" value="<?= $state===true?1:'' ?>">
                <input type="hidden" id="md_<?= $m['id'] ?>" name="menu_denied[<?= $m['id'] ?>]" value="<?= $state===false?1:'' ?>">
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
<div class="col-md-6">
<div class="tm-card p-3">
    <h6>Action Permissions (CRUD)</h6>
    <table class="table table-sm">
        <thead><tr><th>Permission</th><th class="text-center">Allow</th><th class="text-center">Deny</th><th class="text-center">Default</th></tr></thead>
        <tbody>
        <?php foreach ($permissions as $p): $state = $permMap[$p['id']] ?? null; ?>
            <tr>
                <td><?= e($p['label']) ?> <span class="text-muted small">(<?= e($p['module_key']) ?>)</span></td>
                <td class="text-center"><input type="radio" name="perm_state[<?= $p['id'] ?>]" onclick="document.getElementById('pa_<?= $p['id'] ?>').value=1;document.getElementById('pd_<?= $p['id'] ?>').value='';" <?= $state===true?'checked':'' ?>></td>
                <td class="text-center"><input type="radio" name="perm_state[<?= $p['id'] ?>]" onclick="document.getElementById('pd_<?= $p['id'] ?>').value=1;document.getElementById('pa_<?= $p['id'] ?>').value='';" <?= $state===false?'checked':'' ?>></td>
                <td class="text-center"><input type="radio" name="perm_state[<?= $p['id'] ?>]" onclick="document.getElementById('pa_<?= $p['id'] ?>').value='';document.getElementById('pd_<?= $p['id'] ?>').value='';" <?= $state===null?'checked':'' ?>></td>
                <input type="hidden" id="pa_<?= $p['id'] ?>" name="perm_allowed[<?= $p['id'] ?>]" value="<?= $state===true?1:'' ?>">
                <input type="hidden" id="pd_<?= $p['id'] ?>" name="perm_denied[<?= $p['id'] ?>]" value="<?= $state===false?1:'' ?>">
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
</div>
<div class="mt-3"><button class="btn btn-tm text-white">Save Permissions</button></div>
</form>
