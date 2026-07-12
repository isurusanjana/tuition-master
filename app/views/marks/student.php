<h4 class="mb-3">My Marks</h4>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Exam</th><th>Marks</th><th>Grade</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($marks as $m): ?>
        <tr>
            <td><?= e($m['exam_title']) ?></td>
            <td><?= e($m['marks_obtained']) ?> / <?= e($m['total_marks']) ?></td>
            <td><?= e($m['grade']) ?></td>
            <td><?= format_date($m['exam_date']) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($marks)): ?><tr><td colspan="4" class="text-muted text-center">No marks recorded yet.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
