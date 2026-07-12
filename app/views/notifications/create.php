<h4 class="mb-3">New Notification</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('notifications.store') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
                <option value="info">Info</option><option value="success">Success</option>
                <option value="warning">Warning</option><option value="danger">Urgent</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Target Role (optional)</label>
            <select name="target_role_id" class="form-select">
                <option value="">-- everyone --</option>
                <?php foreach ($roles as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-12"><label class="form-label">Title *</label><input name="title" class="form-control" required></div>
        <div class="col-12"><label class="form-label">Message *</label><textarea name="message" class="form-control" rows="3" required></textarea></div>
        <?php if (Auth::isSuperAdmin()): ?>
        <div class="col-12 form-check">
            <input class="form-check-input" type="checkbox" name="broadcast_all" value="1" id="broadcastAll">
            <label class="form-check-label" for="broadcastAll">Broadcast to ALL tuition centers system-wide</label>
        </div>
        <?php endif; ?>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Post Notification</button></div>
</form>
</div>
