<h4 class="mb-1"><?= e($lesson['title']) ?></h4>
<p class="text-muted"><?= e($lesson['description']) ?></p>
<div class="tm-card p-4">
<?php if ($lesson['resource_type'] === 'video' && $lesson['file_path']): ?>
    <video controls class="w-100" style="max-height:480px;"><source src="<?= asset('uploads/'.e($lesson['file_path'])) ?>"></video>
<?php elseif ($lesson['resource_type'] === 'pdf' && $lesson['file_path']): ?>
    <iframe src="<?= asset('uploads/'.e($lesson['file_path'])) ?>" style="width:100%;height:600px;border:0;"></iframe>
<?php elseif ($lesson['file_path']): ?>
    <a href="<?= asset('uploads/'.e($lesson['file_path'])) ?>" class="btn btn-tm text-white" download><i class="bi bi-download"></i> Download Document</a>
<?php elseif ($lesson['external_url']): ?>
    <a href="<?= e($lesson['external_url']) ?>" target="_blank" class="btn btn-tm text-white"><i class="bi bi-box-arrow-up-right"></i> Open Link</a>
<?php endif; ?>
</div>
