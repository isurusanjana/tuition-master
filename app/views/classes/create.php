<h4 class="mb-3">Add Class</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('classes.store') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Class Name *</label><input name="name" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Grade</label><input name="grade" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Subject</label><input name="subject" class="form-control"></div>
        <div class="col-md-6"><label class="form-label">Capacity</label><input type="number" name="capacity" class="form-control"></div>
        <div class="col-12"><label class="form-label">Schedule</label><input name="schedule" class="form-control" placeholder="e.g. Mon, Wed 4-6 PM"></div>
        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Create Class</button></div>
</form>
</div>
