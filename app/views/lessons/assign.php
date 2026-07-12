<h4 class="mb-3">Assign Lesson - <?= e($lesson['title']) ?></h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('lessons.assign', ['id' => $lesson['id']]) ?>">
    <?= csrf_field() ?>
    <p class="text-muted">Select students under your management to assign this lesson to.</p>
    <div class="row">
    <?php foreach ($students as $s): ?>
        <div class="col-md-4 form-check">
            <input class="form-check-input" type="checkbox" name="user_ids[]" value="<?= $s['id'] ?>" id="u<?= $s['id'] ?>" <?= in_array($s['id'],$assignedIds)?'checked':'' ?>>
            <label class="form-check-label" for="u<?= $s['id'] ?>"><?= e($s['first_name'].' '.$s['last_name']) ?></label>
        </div>
    <?php endforeach; ?>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Save Assignment</button></div>
</form>
</div>
