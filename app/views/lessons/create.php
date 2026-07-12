<h4 class="mb-3">Add Lesson</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('lessons.store') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Class (optional)</label>
            <select name="class_id" class="form-select">
                <option value="">-- none / all classes --</option>
                <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Resource Type *</label>
            <select name="resource_type" id="resType" class="form-select" required onchange="document.getElementById('urlWrap').style.display=this.value==='link'?'block':'none';document.getElementById('fileWrap').style.display=this.value==='link'?'none':'block';">
                <option value="document">Document</option>
                <option value="pdf">PDF</option>
                <option value="video">Video</option>
                <option value="link">External Link</option>
            </select>
        </div>
        <div class="col-12"><label class="form-label">Title *</label><input name="title" class="form-control" required></div>
        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        <div class="col-12" id="fileWrap"><label class="form-label">File (PDF/Doc/Video, max 100MB)</label><input type="file" name="resource_file" class="form-control"></div>
        <div class="col-12" id="urlWrap" style="display:none"><label class="form-label">External URL</label><input name="external_url" class="form-control" placeholder="https://..."></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Upload Lesson</button></div>
</form>
</div>
