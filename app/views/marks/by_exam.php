<h4 class="mb-1">Marks - <?= e($exam['title']) ?></h4>
<p class="text-muted">Total Marks: <?= e($exam['total_marks']) ?> &middot; Pass Marks: <?= e($exam['pass_marks']) ?></p>
<form method="POST" action="<?= route('marks.save', ['examId' => $exam['id']]) ?>">
    <?= csrf_field() ?>
    <div class="tm-card p-3">
    <table class="table">
        <thead><tr><th>Student</th><th>Marks Obtained</th><th>Grade</th><th>Remarks</th></tr></thead>
        <tbody>
        <?php foreach ($students as $s): $existing = $marksByStudent[$s['id']] ?? null; ?>
            <tr>
                <td><?= e($s['first_name'].' '.$s['last_name']) ?></td>
                <td><input type="number" step="0.01" max="<?= e($exam['total_marks']) ?>" name="marks[<?= $s['id'] ?>]" class="form-control form-control-sm" value="<?= e($existing['marks_obtained'] ?? '') ?>"></td>
                <td><input type="text" name="grade[<?= $s['id'] ?>]" class="form-control form-control-sm" style="width:80px" value="<?= e($existing['grade'] ?? '') ?>"></td>
                <td><input type="text" name="remarks[<?= $s['id'] ?>]" class="form-control form-control-sm" value="<?= e($existing['remarks'] ?? '') ?>"></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($students)): ?><tr><td colspan="4" class="text-muted text-center">No students to grade.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    <div class="mt-3"><button class="btn btn-tm text-white">Save Marks</button></div>
</form>
