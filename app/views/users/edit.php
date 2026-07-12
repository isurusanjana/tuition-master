<h4 class="mb-3">Edit User</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('users.update', ['id' => $user['id']]) ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Role</label>
            <select name="role_id" class="form-select">
                <?php foreach ($roles as $r): ?><option value="<?= $r['id'] ?>" <?= $r['id']==$user['role_id']?'selected':'' ?>><?= e($r['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?= $user['status']==='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= $user['status']==='inactive'?'selected':'' ?>>Inactive</option>
                <option value="suspended" <?= $user['status']==='suspended'?'selected':'' ?>>Suspended</option>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">First Name</label><input name="first_name" class="form-control" value="<?= e($user['first_name']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Last Name</label><input name="last_name" class="form-control" value="<?= e($user['last_name']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= e($user['phone']) ?>"></div>
        <div class="col-md-6"><label class="form-label">New Password</label><input type="password" name="password" class="form-control" placeholder="Leave blank to keep current"></div>
        <div class="col-md-6"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control" value="<?= e($user['dob']) ?>"></div>
        <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"><?= e($user['address']) ?></textarea></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Save Changes</button></div>
</form>
</div>
