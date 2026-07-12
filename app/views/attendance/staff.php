<h4 class="mb-3">Staff Attendance</h4>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3"><input type="date" name="date" class="form-control" value="<?= e($date) ?>" onchange="this.form.submit()"></div>
</form>
<form method="POST" action="<?= route('attendance.mark_staff') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="date" value="<?= e($date) ?>">
    <div class="tm-card p-3">
    <table class="table">
        <thead><tr><th>Staff</th><th>Status</th><th>Remarks</th></tr></thead>
        <tbody>
        <?php foreach ($staff as $s):
            $existing = null;
            foreach ($records as $r) { if ($r['staff_id']==$s['id']) { $existing = $r; break; } }
        ?>
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
        </tbody>
    </table>
    </div>
    <div class="mt-3"><button class="btn btn-tm text-white">Save</button></div>
</form>
