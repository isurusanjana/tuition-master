<h4 class="mb-3">Help & Tutorials</h4>
<div class="row g-3">
<div class="col-md-3">
    <div class="tm-card p-3">
        <h6>Modules</h6>
        <div class="list-group">
            <a href="<?= url('/help') ?>" class="list-group-item list-group-item-action <?= !$activeModule?'active':'' ?>">All</a>
            <?php foreach ($modules as $m): ?>
                <a href="<?= url('/help?module='.$m['module_key']) ?>" class="list-group-item list-group-item-action <?= $activeModule===$m['module_key']?'active':'' ?>"><?= e(ucfirst(str_replace('_',' ',$m['module_key']))) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<div class="col-md-9">
    <?php foreach ($articles as $a): ?>
        <div class="tm-card p-3 mb-3">
            <h6><i class="bi bi-question-circle help-tip"></i> <?= e($a['title']) ?></h6>
            <p class="mb-1"><?= nl2br(e($a['content'])) ?></p>
            <?php if ($a['video_url']): ?><a href="<?= e($a['video_url']) ?>" target="_blank" class="small">Watch tutorial video &rarr;</a><?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if (empty($articles)): ?><p class="text-muted">No help articles for this module yet.</p><?php endif; ?>
</div>
</div>
