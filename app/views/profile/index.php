<h4 class="mb-3">My Profile</h4>
<div class="row g-3">
<div class="col-md-6">
<div class="tm-card p-4">
    <h6 class="mb-3">Profile Information</h6>
    <form method="POST" action="<?= route('profile.update') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">First Name</label><input name="first_name" class="form-control" value="<?= e($user['first_name']) ?>"></div>
            <div class="col-md-6"><label class="form-label">Last Name</label><input name="last_name" class="form-control" value="<?= e($user['last_name']) ?>"></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= e($user['phone']) ?>"></div>
            <div class="col-md-6"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control" value="<?= e($user['dob']) ?>"></div>
            <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"><?= e($user['address']) ?></textarea></div>
            <div class="col-12"><label class="form-label">Photo</label><input type="file" name="photo" class="form-control"></div>
        </div>
        <div class="mt-3"><button class="btn btn-tm text-white">Save Profile</button></div>
    </form>
</div>
</div>
<div class="col-md-6">
<div class="tm-card p-4">
    <h6 class="mb-3">Change Password</h6>
    <form method="POST" action="<?= route('profile.password') ?>">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
        <button class="btn btn-tm text-white">Change Password</button>
    </form>
</div>
</div>
</div>
