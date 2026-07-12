<h4 class="mb-3">Add Exam</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('exams.store') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Class *</label>
            <select name="class_id" class="form-select" required>
                <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">Exam Date *</label><input type="date" name="exam_date" class="form-control" required></div>
        <div class="col-md-12"><label class="form-label">Title *</label><input name="title" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Total Marks</label><input type="number" step="0.01" name="total_marks" class="form-control" value="100"></div>
        <div class="col-md-6"><label class="form-label">Pass Marks</label><input type="number" step="0.01" name="pass_marks" class="form-control" value="40"></div>
        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Create Exam</button></div>
</form>
</div>
