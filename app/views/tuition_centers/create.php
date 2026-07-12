<h4 class="mb-3">Add Tuition Center</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('tuition_centers.store') ?>">
    <?= csrf_field() ?>
    <h6 class="text-muted">Center Details</h6>
    <div class="row g-3 mb-4">
        <div class="col-md-6"><label class="form-label">Center Name *</label><input name="name" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Unique Code *</label><input name="code" class="form-control" required placeholder="e.g. ABC-COLOMBO"></div>
        <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control"></div>
        <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
    </div>
    <h6 class="text-muted">Tuition Center Admin Account</h6>
    <p class="small text-muted">This user will log in and manage everything within this tuition center.</p>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">First Name *</label><input name="admin_first_name" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Last Name</label><input name="admin_last_name" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="admin_email" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Username *</label><input name="admin_username" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Password *</label><input type="password" name="admin_password" class="form-control" required></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Create Tuition Center</button></div>
</form>
</div>
