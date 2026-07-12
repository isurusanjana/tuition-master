<h4 class="mb-3">Edit Tuition Center</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('tuition_centers.update', ['id' => $center['id']]) ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Center Name</label><input name="name" class="form-control" value="<?= e($center['name']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($center['email']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= e($center['phone']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?= $center['status']==='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= $center['status']==='inactive'?'selected':'' ?>>Inactive</option>
                <option value="suspended" <?= $center['status']==='suspended'?'selected':'' ?>>Suspended</option>
            </select>
        </div>
        <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"><?= e($center['address']) ?></textarea></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Save Changes</button></div>
</form>
</div>
