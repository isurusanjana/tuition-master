<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Special Notes</h4>
    <?php if (Permission::can('notes','add')): ?>
    <a href="<?= route('notes.create') ?>" class="btn btn-tm text-white"><i class="bi bi-plus-lg"></i> Add Note</a>
    <?php endif; ?>
</div>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Title</th><th>Student</th><th>Type</th><th>Visibility</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($notes as $n): ?>
        <tr>
            <td><?= e($n['title']) ?></td>
            <td><?= e($n['student_name'] ?? '-') ?></td>
            <td><span class="badge bg-info-subtle text-dark"><?= e($n['note_type']) ?></span></td>
            <td><?= e($n['visibility']) ?></td>
            <td><?= format_date($n['created_at']) ?></td>
            <td class="text-end">
                <?php if (Permission::can('notes','edit')): ?><a href="<?= route('notes.edit', ['id' => $n['id']]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a><?php endif; ?>
                <?php if (Permission::can('notes','delete')): ?>
                <form class="d-inline" method="POST" action="<?= route('notes.delete', ['id' => $n['id']]) ?>" data-confirm="Delete this note?">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
