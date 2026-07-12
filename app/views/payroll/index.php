<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Payroll</h4>
    <?php if (Permission::can('payroll','add')): ?>
    <a href="<?= route('payroll.create') ?>" class="btn btn-tm text-white"><i class="bi bi-plus-lg"></i> Add Payroll Entry</a>
    <?php endif; ?>
</div>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Staff</th><th>Period</th><th>Basic</th><th>Allowances</th><th>Deductions</th><th>Net</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($records as $r): ?>
        <tr>
            <td><?= e($r['first_name'].' '.$r['last_name']) ?></td>
            <td><?= e(date('F', mktime(0,0,0,$r['pay_period_month'],1))) ?> <?= e($r['pay_period_year']) ?></td>
            <td><?= number_format($r['basic_salary'],2) ?></td>
            <td><?= number_format($r['allowances'],2) ?></td>
            <td><?= number_format($r['deductions'],2) ?></td>
            <td><strong><?= number_format($r['net_salary'],2) ?></strong></td>
            <td><span class="badge bg-<?= $r['status']==='paid'?'success':'warning' ?>"><?= e($r['status']) ?></span></td>
            <td class="text-end">
                <?php if ($r['status']!=='paid' && Permission::can('payroll','edit')): ?>
                <form class="d-inline" method="POST" action="<?= route('payroll.mark_paid', ['id' => $r['id']]) ?>">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-success">Mark Paid</button>
                </form>
                <a href="<?= route('payroll.edit', ['id' => $r['id']]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <?php endif; ?>
                <?php if (Permission::can('payroll','delete')): ?>
                <form class="d-inline" method="POST" action="<?= route('payroll.delete', ['id' => $r['id']]) ?>" data-confirm="Delete this entry?">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
