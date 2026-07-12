<h4 class="mb-1"><?= e($exam['title']) ?></h4>
<p class="text-muted">Class: <?= e($class['name'] ?? '') ?> &middot; Date: <?= format_date($exam['exam_date']) ?> &middot; Total: <?= e($exam['total_marks']) ?></p>

<?php if (Permission::can('exams','assign')): ?>
<div class="tm-card p-3 mb-3">
    <h6>Assign Exam to Specific Students <span class="text-muted small">(leave all unchecked to make it visible to the whole class)</span></h6>
    <form method="POST" action="<?= route('exams.assign', ['id' => $exam['id']]) ?>">
        <?= csrf_field() ?>
        <div class="row">
        <?php foreach ($students as $s): ?>
            <div class="col-md-4 form-check">
                <input class="form-check-input" type="checkbox" name="student_ids[]" value="<?= $s['id'] ?>" id="st<?= $s['id'] ?>" <?= in_array($s['id'],$assignedIds)?'checked':'' ?>>
                <label class="form-check-label" for="st<?= $s['id'] ?>"><?= e($s['first_name'].' '.$s['last_name']) ?></label>
            </div>
        <?php endforeach; ?>
        </div>
        <button class="btn btn-sm btn-tm text-white mt-2">Save Assignment</button>
    </form>
</div>
<?php endif; ?>

<div class="tm-card p-3">
    <div class="d-flex justify-content-between">
        <h6>Marks</h6>
        <a href="<?= route('marks.by_exam', ['examId' => $exam['id']]) ?>" class="btn btn-sm btn-outline-success">Enter / Edit Marks</a>
    </div>
    <table class="table table-sm mt-2">
        <thead><tr><th>Student</th><th>Marks</th><th>Grade</th></tr></thead>
        <tbody>
        <?php foreach ($marks as $m): ?>
            <tr><td><?= e($m['first_name'].' '.$m['last_name']) ?></td><td><?= e($m['marks_obtained']) ?></td><td><?= e($m['grade']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (empty($marks)): ?><tr><td colspan="3" class="text-muted text-center">No marks recorded yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
