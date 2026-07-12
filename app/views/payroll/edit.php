<h4 class="mb-3">Edit Payroll Entry</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('payroll.update', ['id' => $record['id']]) ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Staff</label>
            <select name="staff_id" class="form-select">
                <?php foreach ($staff as $s): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$record['staff_id']?'selected':'' ?>><?= e($s['first_name'].' '.$s['last_name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Month</label>
            <select name="pay_period_month" class="form-select">
                <?php for ($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= $m==$record['pay_period_month']?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option><?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Year</label><input type="number" name="pay_period_year" class="form-control" value="<?= e($record['pay_period_year']) ?>"></div>
        <div class="col-md-4"><label class="form-label">Basic Salary</label><input type="number" step="0.01" name="basic_salary" class="form-control" value="<?= e($record['basic_salary']) ?>"></div>
        <div class="col-md-4"><label class="form-label">Allowances</label><input type="number" step="0.01" name="allowances" class="form-control" value="<?= e($record['allowances']) ?>"></div>
        <div class="col-md-4"><label class="form-label">Deductions</label><input type="number" step="0.01" name="deductions" class="form-control" value="<?= e($record['deductions']) ?>"></div>
        <div class="col-12"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="2"><?= e($record['remarks']) ?></textarea></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Save Changes</button></div>
</form>
</div>
