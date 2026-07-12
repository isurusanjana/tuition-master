<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Exams</h4>
    <?php if (Permission::can('exams','add')): ?>
    <a href="<?= route('exams.create') ?>" class="btn btn-tm text-white"><i class="bi bi-plus-lg"></i> Add Exam</a>
    <?php endif; ?>
</div>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Title</th><th>Class</th><th>Date</th><th>Total Marks</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($exams as $ex): ?>
        <tr>
            <td><a href="<?= route('exams.show', ['id' => $ex['id']]) ?>"><?= e($ex['title']) ?></a></td>
            <td><?= e($ex['class_name'] ?? '') ?></td>
            <td><?= format_date($ex['exam_date']) ?></td>
            <td><?= e($ex['total_marks']) ?></td>
            <td><span class="badge bg-info-subtle text-dark"><?= e($ex['status']) ?></span></td>
            <td class="text-end">
                <a href="<?= route('marks.by_exam', ['examId' => $ex['id']]) ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-award"></i> Marks</a>
                <?php if (Permission::can('exams','edit')): ?><a href="<?= route('exams.edit', ['id' => $ex['id']]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a><?php endif; ?>
                <?php if (Permission::can('exams','delete')): ?>
                <form class="d-inline" method="POST" action="<?= route('exams.delete', ['id' => $ex['id']]) ?>" data-confirm="Delete this exam?">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
