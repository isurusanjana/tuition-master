<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Welcome back, <?= e(Auth::user()['first_name'] ?? '') ?> 👋</h4>
        <p class="text-muted mb-0"><?= e(Auth::user()['role_name'] ?? '') ?><?= Auth::centerId() ? '' : (Auth::isSuperAdmin() ? ' &middot; All Tuition Centers' : '') ?></p>
    </div>
</div>

<?php if (Auth::isSuperAdmin()): ?>
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6"><div class="tm-stat-card c1"><div class="small">Tuition Centers</div><h3><?= (int) ($stats['centers'] ?? 0) ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c2"><div class="small">Active Centers</div><h3><?= (int) ($stats['active_centers'] ?? 0) ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c3"><div class="small">Total Users</div><h3><?= (int) ($stats['users'] ?? 0) ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c4"><div class="small">Total Classes</div><h3><?= (int) ($stats['classes'] ?? 0) ?></h3></div></div>
</div>

<?php elseif (in_array(Auth::roleSlug(), ['center_admin','admin_staff'])): ?>
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6"><div class="tm-stat-card c1"><div class="small">Students</div><h3><?= (int) ($stats['students'] ?? 0) ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c2"><div class="small">Teachers</div><h3><?= (int) ($stats['teachers'] ?? 0) ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c3"><div class="small">Classes</div><h3><?= (int) ($stats['classes'] ?? 0) ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c4"><div class="small">Exams</div><h3><?= (int) ($stats['exams'] ?? 0) ?></h3></div></div>
</div>

<?php elseif (Auth::roleSlug() === 'teacher'): ?>
<div class="row g-3 mb-4">
    <div class="col-md-4 col-6"><div class="tm-stat-card c1"><div class="small">My Classes</div><h3><?= (int) ($stats['classes'] ?? 0) ?></h3></div></div>
    <div class="col-md-4 col-6"><div class="tm-stat-card c2"><div class="small">My Students</div><h3><?= (int) ($stats['students'] ?? 0) ?></h3></div></div>
    <div class="col-md-4 col-6"><div class="tm-stat-card c3"><div class="small">Exams</div><h3><?= (int) ($stats['exams'] ?? 0) ?></h3></div></div>
</div>

<?php elseif (Auth::roleSlug() === 'student'): ?>
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6"><div class="tm-stat-card c1"><div class="small">Present</div><h3><?= (int) ($stats['attendance']['present_count'] ?? 0) ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c3"><div class="small">Absent</div><h3><?= (int) ($stats['attendance']['absent_count'] ?? 0) ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c2"><div class="small">Late</div><h3><?= (int) ($stats['attendance']['late_count'] ?? 0) ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c4"><div class="small">Total Days</div><h3><?= (int) ($stats['attendance']['total'] ?? 0) ?></h3></div></div>
</div>
<div class="tm-card p-3 mb-4">
    <h6 class="mb-3">Recent Marks</h6>
    <table class="table table-sm mb-0">
        <thead><tr><th>Exam</th><th>Marks</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach (($stats['recent_marks'] ?? []) as $m): ?>
            <tr><td><?= e($m['exam_title']) ?></td><td><?= e($m['marks_obtained']) ?> / <?= e($m['total_marks']) ?></td><td><?= format_date($m['exam_date']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (empty($stats['recent_marks'])): ?><tr><td colspan="3" class="text-muted text-center">No marks yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php elseif (Auth::roleSlug() === 'parent'): ?>
<div class="tm-card p-3 mb-4">
    <h6 class="mb-3">My Children</h6>
    <div class="row g-3">
    <?php foreach (($stats['children'] ?? []) as $c): ?>
        <div class="col-md-4">
            <div class="border rounded p-3">
                <strong><?= e($c['first_name'] . ' ' . $c['last_name']) ?></strong>
                <div class="mt-2"><a href="<?= url('/reports/student/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary">View Report</a></div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($stats['children'])): ?><p class="text-muted">No linked children yet.</p><?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="tm-card p-3">
    <h6 class="mb-3"><i class="bi bi-bell me-1"></i> Latest Notifications</h6>
    <?php if (empty($notifications)): ?>
        <p class="text-muted mb-0">No notifications right now.</p>
    <?php endif; ?>
    <?php foreach ($notifications as $n): ?>
        <div class="alert alert-<?= e($n['type']) ?> py-2 mb-2">
            <strong><?= e($n['title']) ?></strong> — <?= e($n['message']) ?>
        </div>
    <?php endforeach; ?>
</div>
