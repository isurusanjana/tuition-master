<h4 class="mb-3">Student Summary Reports</h4>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Student</th><th>Email</th><th class="text-end">Action</th></tr></thead>
    <tbody>
    <?php foreach ($students as $s): ?>
        <tr>
            <td><?= e($s['first_name'].' '.$s['last_name']) ?></td>
            <td><?= e($s['email'] ?? '') ?></td>
            <td class="text-end"><a href="<?= route('reports.student', ['id' => $s['id']]) ?>" class="btn btn-sm btn-tm text-white">View Report</a></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($students)): ?><tr><td colspan="3" class="text-muted text-center">No students found.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
