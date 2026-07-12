<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Tuition Centers</h4>
    <a href="<?= route('tuition_centers.create') ?>" class="btn btn-tm text-white"><i class="bi bi-plus-lg"></i> Add Tuition Center</a>
</div>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Name</th><th>Code</th><th>Email</th><th>Phone</th><th>Status</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($centers as $c): ?>
        <tr>
            <td><a href="<?= route('tuition_centers.show', ['id' => $c['id']]) ?>"><?= e($c['name']) ?></a></td>
            <td><?= e($c['code']) ?></td>
            <td><?= e($c['email']) ?></td>
            <td><?= e($c['phone']) ?></td>
            <td><span class="badge bg-<?= $c['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e($c['status']) ?></span></td>
            <td><?= format_date($c['created_at']) ?></td>
            <td class="text-end">
                <a href="<?= route('tuition_centers.edit', ['id' => $c['id']]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form class="d-inline" method="POST" action="<?= route('tuition_centers.delete', ['id' => $c['id']]) ?>" data-confirm="Delete this tuition center and ALL its data?">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
