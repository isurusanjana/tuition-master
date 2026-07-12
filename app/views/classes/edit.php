<h4 class="mb-3">Edit Class</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('classes.update', ['id' => $class['id']]) ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Class Name</label><input name="name" class="form-control" value="<?= e($class['name']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Grade</label><input name="grade" class="form-control" value="<?= e($class['grade']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Subject</label><input name="subject" class="form-control" value="<?= e($class['subject']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Capacity</label><input type="number" name="capacity" class="form-control" value="<?= e($class['capacity']) ?>"></div>
        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?= $class['status']==='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= $class['status']==='inactive'?'selected':'' ?>>Inactive</option>
            </select>
        </div>
        <div class="col-12"><label class="form-label">Schedule</label><input name="schedule" class="form-control" value="<?= e($class['schedule']) ?>"></div>
        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= e($class['description']) ?></textarea></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Save Changes</button></div>
</form>
</div>
