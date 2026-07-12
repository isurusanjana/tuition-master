<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Lesson Tools</h4>
    <?php if (Permission::can('lessons','add')): ?>
    <a href="<?= route('lessons.create') ?>" class="btn btn-tm text-white"><i class="bi bi-plus-lg"></i> Add Lesson</a>
    <?php endif; ?>
</div>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Title</th><th>Type</th><th>Uploaded</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($lessons as $l): ?>
        <tr>
            <td><a href="<?= route('lessons.show', ['id' => $l['id']]) ?>"><?= e($l['title']) ?></a>
                <?php if (isset($l['is_viewed']) && !$l['is_viewed']): ?><span class="badge bg-danger">New</span><?php endif; ?>
            </td>
            <td><span class="badge bg-info-subtle text-dark"><i class="bi bi-<?= $l['resource_type']==='video'?'camera-video':($l['resource_type']==='pdf'?'file-earmark-pdf':'file-earmark-text') ?>"></i> <?= e($l['resource_type']) ?></span></td>
            <td><?= format_date($l['created_at']) ?></td>
            <td class="text-end">
                <?php if (Permission::can('lessons','assign')): ?><a href="<?= route('lessons.assign_form', ['id' => $l['id']]) ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-people"></i></a><?php endif; ?>
                <?php if (Permission::can('lessons','edit')): ?><a href="<?= route('lessons.edit', ['id' => $l['id']]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a><?php endif; ?>
                <?php if (Permission::can('lessons','delete')): ?>
                <form class="d-inline" method="POST" action="<?= route('lessons.delete', ['id' => $l['id']]) ?>" data-confirm="Delete this lesson?">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($lessons)): ?><tr><td colspan="4" class="text-muted text-center">No lessons yet.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
