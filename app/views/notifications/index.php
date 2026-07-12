<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Notifications</h4>
    <?php if (Permission::can('notifications','add')): ?>
    <a href="<?= route('notifications.create') ?>" class="btn btn-tm text-white"><i class="bi bi-plus-lg"></i> New Notification</a>
    <?php endif; ?>
</div>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Title</th><th>Message</th><th>Type</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($notifications as $n): ?>
        <tr>
            <td><?= e($n['title']) ?></td>
            <td><?= e(mb_strimwidth($n['message'],0,80,'...')) ?></td>
            <td><span class="badge bg-<?= e($n['type']) ?>"><?= e($n['type']) ?></span></td>
            <td><?= format_date($n['created_at']) ?></td>
            <td class="text-end">
                <?php if (Permission::can('notifications','delete')): ?>
                <form class="d-inline" method="POST" action="<?= route('notifications.delete', ['id' => $n['id']]) ?>" data-confirm="Remove this notification?">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
