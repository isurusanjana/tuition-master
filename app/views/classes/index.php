<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Classes</h4>
    <?php if (Permission::can('classes','add')): ?>
    <a href="<?= route('classes.create') ?>" class="btn btn-tm text-white"><i class="bi bi-plus-lg"></i> Add Class</a>
    <?php endif; ?>
</div>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Name</th><th>Grade</th><th>Subject</th><th>Schedule</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($classes as $c): ?>
        <tr>
            <td><a href="<?= route('classes.show', ['id' => $c['id']]) ?>"><?= e($c['name']) ?></a></td>
            <td><?= e($c['grade']) ?></td>
            <td><?= e($c['subject']) ?></td>
            <td><?= e($c['schedule']) ?></td>
            <td><span class="badge bg-<?= $c['status']==='active'?'success':'secondary' ?>"><?= e($c['status']) ?></span></td>
            <td class="text-end">
                <a href="<?= route('classes.show', ['id' => $c['id']]) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                <?php if (Permission::can('classes','edit')): ?><a href="<?= route('classes.edit', ['id' => $c['id']]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a><?php endif; ?>
                <?php if (Permission::can('classes','delete')): ?>
                <form class="d-inline" method="POST" action="<?= route('classes.delete', ['id' => $c['id']]) ?>" data-confirm="Delete this class?">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
