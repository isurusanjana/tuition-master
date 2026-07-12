<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Attendance</h4>
    <div>
        <a href="<?= route('attendance.mark_form') ?>" class="btn btn-tm text-white"><i class="bi bi-check2-square"></i> Mark Attendance</a>
        <a href="<?= route('attendance.staff_index') ?>" class="btn btn-outline-secondary"><i class="bi bi-person-badge"></i> Staff Attendance</a>
    </div>
</div>
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
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Student</th><th>Status</th><th>Remarks</th></tr></thead>
    <tbody>
    <?php foreach ($records as $r): ?>
        <tr>
            <td><?= e($r['first_name'].' '.$r['last_name']) ?></td>
            <td><span class="badge bg-<?= $r['status']==='present'?'success':($r['status']==='absent'?'danger':'warning') ?>"><?= e($r['status']) ?></span></td>
            <td><?= e($r['remarks']) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($records)): ?><tr><td colspan="3" class="text-muted text-center">No attendance recorded for this date.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
