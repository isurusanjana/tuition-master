<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Users</h4>
    <a href="<?= route('users.create') ?>" class="btn btn-tm text-white"><i class="bi bi-plus-lg"></i> Add User</a>
</div>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Name</th><th>Role</th><th>Email</th><th>Username</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><a href="<?= route('users.show', ['id' => $u['id']]) ?>"><?= e($u['first_name'].' '.$u['last_name']) ?></a></td>
            <td><span class="badge bg-info-subtle text-dark"><?= e($u['role_name']) ?></span></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['username']) ?></td>
            <td><span class="badge bg-<?= $u['status']==='active'?'success':'secondary' ?>"><?= e($u['status']) ?></span></td>
            <td class="text-end">
                <a href="<?= route('users.show', ['id' => $u['id']]) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                <a href="<?= route('users.edit', ['id' => $u['id']]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <a href="<?= route('users.permissions', ['id' => $u['id']]) ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-shield-lock"></i></a>
                <form class="d-inline" method="POST" action="<?= route('users.delete', ['id' => $u['id']]) ?>" data-confirm="Delete this user?">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
