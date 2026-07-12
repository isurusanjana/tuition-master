<h4 class="mb-3">Edit Note</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('notes.update', ['id' => $note['id']]) ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Student</label>
            <select name="student_id" class="form-select">
                <option value="">-- general note --</option>
                <?php foreach ($students as $s): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$note['student_id']?'selected':'' ?>><?= e($s['first_name'].' '.$s['last_name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Type</label>
            <select name="note_type" class="form-select">
                <?php foreach (['general','behavioral','academic','medical'] as $t): ?><option value="<?= $t ?>" <?= $note['note_type']===$t?'selected':'' ?>><?= ucfirst($t) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-12"><label class="form-label">Title</label><input name="title" class="form-control" value="<?= e($note['title']) ?>" required></div>
        <div class="col-12"><label class="form-label">Content</label><textarea name="content" class="form-control" rows="4" required><?= e($note['content']) ?></textarea></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Save Changes</button></div>
</form>
</div>
