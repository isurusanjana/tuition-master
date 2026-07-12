<h4 class="mb-3">Add Special Note</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('notes.store') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Student (optional)</label>
            <select name="student_id" class="form-select">
                <option value="">-- general note --</option>
                <?php foreach ($students as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['first_name'].' '.$s['last_name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Class (optional)</label>
            <select name="class_id" class="form-select">
                <option value="">-- none --</option>
                <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Type</label>
            <select name="note_type" class="form-select">
                <?php foreach (['general','behavioral','academic','medical'] as $t): ?><option value="<?= $t ?>"><?= ucfirst($t) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Visibility</label>
            <select name="visibility" class="form-select">
                <option value="private">Private (only me)</option>
                <option value="staff" selected>Staff</option>
                <option value="parents">Staff + Parents</option>
            </select>
        </div>
        <div class="col-12"><label class="form-label">Title *</label><input name="title" class="form-control" required></div>
        <div class="col-12"><label class="form-label">Content *</label><textarea name="content" class="form-control" rows="4" required></textarea></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Save Note</button></div>
</form>
</div>
