<h4 class="mb-3">Marks</h4>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Exam</th><th>Class</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($exams as $ex): ?>
        <tr>
            <td><?= e($ex['title']) ?></td>
            <td><?= e($ex['class_name'] ?? '') ?></td>
            <td><?= format_date($ex['exam_date']) ?></td>
            <td class="text-end"><a href="<?= route('marks.by_exam', ['examId' => $ex['id']]) ?>" class="btn btn-sm btn-tm text-white">Enter / View Marks</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
