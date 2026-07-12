<h4 class="mb-3">Add User</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('users.store') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
        <?php if (!empty($centers)): ?>
        <div class="col-md-6">
            <label class="form-label">Tuition Center *</label>
            <select name="tuition_center_id" class="form-select" required>
                <?php foreach ($centers as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="col-md-6">
            <label class="form-label">Role *</label>
            <select name="role_id" class="form-select" required>
                <?php foreach ($roles as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">First Name *</label><input name="first_name" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Last Name</label><input name="last_name" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Username *</label><input name="username" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required></div>
        <div class="col-md-6">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select"><option value="">-- select --</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select>
        </div>
        <div class="col-md-6"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control"></div>
        <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Create User</button></div>
</form>
</div>
