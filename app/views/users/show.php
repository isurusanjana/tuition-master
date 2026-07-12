<div class="d-flex justify-content-between">
<h4 class="mb-3"><?= e($user['first_name'].' '.$user['last_name']) ?></h4>
<div>
<a href="<?= route('users.edit', ['id' => $user['id']]) ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
<a href="<?= route('users.permissions', ['id' => $user['id']]) ?>" class="btn btn-outline-warning btn-sm"><i class="bi bi-shield-lock"></i> Permissions</a>
</div>
</div>
<div class="tm-card p-3">
    <p class="mb-1"><strong>Role:</strong> <?= e($user['role_name']) ?></p>
    <p class="mb-1"><strong>Email:</strong> <?= e($user['email']) ?></p>
    <p class="mb-1"><strong>Phone:</strong> <?= e($user['phone']) ?></p>
    <p class="mb-1"><strong>Username:</strong> <?= e($user['username']) ?></p>
    <p class="mb-1"><strong>Status:</strong> <?= e($user['status']) ?></p>
    <p class="mb-0"><strong>Joined:</strong> <?= format_date($user['created_at']) ?></p>
</div>
