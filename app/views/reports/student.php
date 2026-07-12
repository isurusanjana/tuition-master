<h4 class="mb-3">Summary Report - <?= e($student['first_name'].' '.$student['last_name']) ?></h4>

<div class="row g-3 mb-3">
    <div class="col-md-3 col-6"><div class="tm-stat-card c1"><div class="small">Present</div><h3><?= (int) ($attendance['present_count'] ?? 0) ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c3"><div class="small">Absent</div><h3><?= (int) ($attendance['absent_count'] ?? 0) ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c2"><div class="small">Late</div><h3><?= (int) ($attendance['late_count'] ?? 0) ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c4"><div class="small">Total Days</div><h3><?= (int) ($attendance['total'] ?? 0) ?></h3></div></div>
</div>

<div class="row g-3">
<div class="col-md-6">
<div class="tm-card p-3 mb-3">
    <h6>Enrolled Classes</h6>
    <ul class="list-group list-group-flush">
        <?php foreach ($classes as $c): ?><li class="list-group-item"><?= e($c['name']) ?></li><?php endforeach; ?>
        <?php if (empty($classes)): ?><li class="list-group-item text-muted">Not enrolled in any class.</li><?php endif; ?>
    </ul>
</div>
<div class="tm-card p-3">
    <h6>Special Notes</h6>
    <?php foreach ($notes as $n): ?>
        <div class="border-bottom py-2">
            <strong><?= e($n['title']) ?></strong> <span class="badge bg-secondary"><?= e($n['note_type']) ?></span>
            <p class="mb-0 small text-muted"><?= e($n['content']) ?></p>
        </div>
    <?php endforeach; ?>
    <?php if (empty($notes)): ?><p class="text-muted mb-0">No notes recorded.</p><?php endif; ?>
</div>
</div>
<div class="col-md-6">
<div class="tm-card p-3 mb-3">
    <h6>Exam Marks</h6>
    <table class="table table-sm">
        <thead><tr><th>Exam</th><th>Marks</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($marks as $m): ?>
            <tr><td><?= e($m['exam_title']) ?></td><td><?= e($m['marks_obtained']) ?>/<?= e($m['total_marks']) ?></td><td><?= format_date($m['exam_date']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (empty($marks)): ?><tr><td colspan="3" class="text-muted text-center">No marks recorded.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<div class="tm-card p-3">
    <h6>Assigned Lessons</h6>
    <ul class="list-group list-group-flush">
        <?php foreach ($lessons as $l): ?>
            <li class="list-group-item d-flex justify-content-between"><?= e($l['title']) ?> <?= $l['is_viewed']?'<span class="badge bg-success">Viewed</span>':'<span class="badge bg-secondary">Pending</span>' ?></li>
        <?php endforeach; ?>
        <?php if (empty($lessons)): ?><li class="list-group-item text-muted">No lessons assigned.</li><?php endif; ?>
    </ul>
</div>
</div>
</div>
