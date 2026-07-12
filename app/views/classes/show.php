<h4 class="mb-3"><?= e($class['name']) ?></h4>
<div class="row g-3">
<div class="col-md-6">
<div class="tm-card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Assigned Teachers</h6>
    </div>
    <?php if (Permission::can('classes','assign')): ?>
    <form method="POST" action="<?= route('classes.assign_teacher', ['id' => $class['id']]) ?>" class="d-flex gap-2 mb-3">
        <?= csrf_field() ?>
        <select name="teacher_id" class="form-select form-select-sm" required>
            <option value="">-- select teacher --</option>
            <?php foreach ($availableTeachers as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['first_name'].' '.$t['last_name']) ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-sm btn-tm text-white">Assign</button>
    </form>
    <?php endif; ?>
    <ul class="list-group">
        <?php foreach ($teachers as $t): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= e($t['first_name'].' '.$t['last_name']) ?>
            <?php if (Permission::can('classes','assign')): ?>
            <form method="POST" action="<?= route('classes.remove_teacher', ['id' => $class['id'], 'tid' => $t['id']]) ?>" data-confirm="Remove this teacher?">
                <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>
            </form>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <?php if (empty($teachers)): ?><li class="list-group-item text-muted">No teachers assigned yet.</li><?php endif; ?>
    </ul>
</div>
</div>
<div class="col-md-6">
<div class="tm-card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Enrolled Students</h6>
    </div>
    <?php if (Permission::can('classes','assign')): ?>
    <form method="POST" action="<?= route('classes.assign_student', ['id' => $class['id']]) ?>" class="d-flex gap-2 mb-3">
        <?= csrf_field() ?>
        <select name="student_id" class="form-select form-select-sm" required>
            <option value="">-- select student --</option>
            <?php foreach ($availableStudents as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['first_name'].' '.$s['last_name']) ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-sm btn-tm text-white">Enroll</button>
    </form>
    <?php endif; ?>
    <ul class="list-group">
        <?php foreach ($students as $s): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <a href="<?= route('users.show', ['id' => $s['id']]) ?>"><?= e($s['first_name'].' '.$s['last_name']) ?></a>
            <?php if (Permission::can('classes','assign')): ?>
            <form method="POST" action="<?= route('classes.remove_student', ['id' => $class['id'], 'sid' => $s['id']]) ?>" data-confirm="Remove this student?">
                <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>
            </form>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <?php if (empty($students)): ?><li class="list-group-item text-muted">No students enrolled yet.</li><?php endif; ?>
    </ul>
</div>
</div>
</div>
