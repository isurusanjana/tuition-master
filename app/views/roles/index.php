<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Roles & Permissions</h4>
    <a href="<?= route('roles.create') ?>" class="btn btn-tm text-white"><i class="bi bi-plus-lg"></i> Add Custom Role</a>
</div>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Role</th><th>Description</th><th>Level</th><th>Type</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($roles as $r): ?>
        <tr>
            <td><?= e($r['name']) ?></td>
            <td><?= e($r['description']) ?></td>
            <td><?= (int) $r['level'] ?></td>
            <td><span class="badge bg-<?= $r['is_system']?'secondary':'info' ?>"><?= $r['is_system']?'System':'Custom' ?></span></td>
            <td class="text-end">
                <a href="<?= route('roles.access', ['id' => $r['id']]) ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-shield-check"></i> Access</a>
                <?php if (!$r['is_system']): ?>
                <a href="<?= route('roles.edit', ['id' => $r['id']]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form class="d-inline" method="POST" action="<?= route('roles.delete', ['id' => $r['id']]) ?>" data-confirm="Delete this role?">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
