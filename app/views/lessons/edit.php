<h4 class="mb-3">Edit Lesson</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('lessons.update', ['id' => $lesson['id']]) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select">
                <option value="">-- none / all classes --</option>
                <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id']==$lesson['class_id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Resource Type</label>
            <select name="resource_type" class="form-select">
                <?php foreach (['document','pdf','video','link'] as $t): ?><option value="<?= $t ?>" <?= $lesson['resource_type']===$t?'selected':'' ?>><?= ucfirst($t) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-12"><label class="form-label">Title</label><input name="title" class="form-control" value="<?= e($lesson['title']) ?>" required></div>
        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= e($lesson['description']) ?></textarea></div>
        <div class="col-12"><label class="form-label">Replace File (optional)</label><input type="file" name="resource_file" class="form-control"></div>
        <div class="col-12"><label class="form-label">External URL</label><input name="external_url" class="form-control" value="<?= e($lesson['external_url']) ?>"></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Save Changes</button></div>
</form>
</div>
