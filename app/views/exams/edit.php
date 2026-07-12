<h4 class="mb-3">Edit Exam</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('exams.update', ['id' => $exam['id']]) ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select">
                <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id']==$exam['class_id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">Exam Date</label><input type="date" name="exam_date" class="form-control" value="<?= e($exam['exam_date']) ?>"></div>
        <div class="col-md-12"><label class="form-label">Title</label><input name="title" class="form-control" value="<?= e($exam['title']) ?>"></div>
        <div class="col-md-4"><label class="form-label">Total Marks</label><input type="number" step="0.01" name="total_marks" class="form-control" value="<?= e($exam['total_marks']) ?>"></div>
        <div class="col-md-4"><label class="form-label">Pass Marks</label><input type="number" step="0.01" name="pass_marks" class="form-control" value="<?= e($exam['pass_marks']) ?>"></div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <?php foreach (['draft','published','completed'] as $st): ?>
                    <option value="<?= $st ?>" <?= $exam['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= e($exam['description']) ?></textarea></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Save Changes</button></div>
</form>
</div>
