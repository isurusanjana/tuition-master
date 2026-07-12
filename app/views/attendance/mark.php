<h4 class="mb-3">Mark Attendance</h4>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <select name="class_id" class="form-select" onchange="this.form.submit()">
            <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id']==$classId?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <input type="date" name="date" class="form-control" value="<?= e($date) ?>" onchange="this.form.submit()">
    </div>
</form>

<?php if ($classId): ?>
<form method="POST" action="<?= route('attendance.mark') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="class_id" value="<?= $classId ?>">
    <input type="hidden" name="date" value="<?= e($date) ?>">
    <div class="tm-card p-3">
    <table class="table">
        <thead><tr><th>Student</th><th>Status</th><th>Remarks</th></tr></thead>
        <tbody>
        <?php foreach ($students as $s): $existing = $existingRecords[$s['id']] ?? null; ?>
            <tr>
                <td><?= e($s['first_name'].' '.$s['last_name']) ?></td>
                <td>
                    <select name="status[<?= $s['id'] ?>]" class="form-select form-select-sm">
                        <?php foreach (['present','absent','late','excused'] as $st): ?>
                            <option value="<?= $st ?>" <?= ($existing['status'] ?? 'present')===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" name="remarks[<?= $s['id'] ?>]" class="form-control form-control-sm" value="<?= e($existing['remarks'] ?? '') ?>"></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($students)): ?><tr><td colspan="3" class="text-muted text-center">No students enrolled in this class.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
    <div class="mt-3"><button class="btn btn-tm text-white">Save Attendance</button></div>
</form>
<?php endif; ?>
