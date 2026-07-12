<h4 class="mb-3">Add Payroll Entry</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('payroll.store') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Staff *</label>
            <select name="staff_id" class="form-select" required>
                <?php foreach ($staff as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['first_name'].' '.$s['last_name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Month *</label>
            <select name="pay_period_month" class="form-select" required>
                <?php for ($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= $m==date('n')?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option><?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Year *</label><input type="number" name="pay_period_year" class="form-control" value="<?= date('Y') ?>" required></div>
        <div class="col-md-4"><label class="form-label">Basic Salary *</label><input type="number" step="0.01" name="basic_salary" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Allowances</label><input type="number" step="0.01" name="allowances" class="form-control" value="0"></div>
        <div class="col-md-4"><label class="form-label">Deductions</label><input type="number" step="0.01" name="deductions" class="form-control" value="0"></div>
        <div class="col-12"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Save Entry</button></div>
</form>
</div>
