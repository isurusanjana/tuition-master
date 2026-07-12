<h4 class="mb-3"><?= e($center['name']) ?> <span class="badge bg-<?= $center['status']==='active'?'success':'secondary' ?>"><?= e($center['status']) ?></span></h4>
<div class="row g-3 mb-3">
    <div class="col-md-3 col-6"><div class="tm-stat-card c1"><div class="small">Total Users</div><h3><?= (int) $counts['users'] ?></h3></div></div>
    <div class="col-md-3 col-6"><div class="tm-stat-card c2"><div class="small">Total Classes</div><h3><?= (int) $counts['classes'] ?></h3></div></div>
</div>
<div class="tm-card p-3 mb-3">
    <h6>Center Info</h6>
    <p class="mb-1"><strong>Code:</strong> <?= e($center['code']) ?></p>
    <p class="mb-1"><strong>Email:</strong> <?= e($center['email']) ?></p>
    <p class="mb-1"><strong>Phone:</strong> <?= e($center['phone']) ?></p>
    <p class="mb-0"><strong>Address:</strong> <?= e($center['address']) ?></p>
</div>
<div class="tm-card p-3">
    <h6>Center Admin(s)</h6>
    <table class="table table-sm">
        <thead><tr><th>Name</th><th>Email</th><th>Username</th></tr></thead>
        <tbody>
        <?php foreach ($admins as $a): ?>
            <tr><td><?= e($a['first_name'].' '.$a['last_name']) ?></td><td><?= e($a['email']) ?></td><td><?= e($a['username']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
