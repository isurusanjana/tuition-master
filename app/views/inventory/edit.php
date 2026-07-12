<h4 class="mb-3">Edit Item - <?= e($item['name']) ?></h4>
<div class="row g-3">
<div class="col-md-6">
<div class="tm-card p-4">
<form method="POST" action="<?= route('inventory.update', ['id' => $item['id']]) ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-12"><label class="form-label">Item Name</label><input name="name" class="form-control" value="<?= e($item['name']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Category</label><input name="category" class="form-control" value="<?= e($item['category']) ?>"></div>
        <div class="col-md-6"><label class="form-label">SKU</label><input name="sku" class="form-control" value="<?= e($item['sku']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Unit</label><input name="unit" class="form-control" value="<?= e($item['unit']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Unit Price</label><input type="number" step="0.01" name="unit_price" class="form-control" value="<?= e($item['unit_price']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Reorder Level</label><input type="number" name="reorder_level" class="form-control" value="<?= e($item['reorder_level']) ?>"></div>
        <div class="col-md-6"><label class="form-label">Location</label><input name="location" class="form-control" value="<?= e($item['location']) ?>"></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Save Changes</button></div>
</form>
</div>
</div>
<div class="col-md-6">
<div class="tm-card p-4 mb-3">
    <h6>Stock Transaction — Current Qty: <?= (int) $item['quantity'] ?> <?= e($item['unit']) ?></h6>
    <form method="POST" action="<?= route('inventory.transaction', ['id' => $item['id']]) ?>" class="row g-2">
        <?= csrf_field() ?>
        <div class="col-4">
            <select name="type" class="form-select">
                <option value="in">Stock In</option>
                <option value="out">Stock Out</option>
            </select>
        </div>
        <div class="col-4"><input type="number" name="quantity" class="form-control" placeholder="Qty" required></div>
        <div class="col-4"><button class="btn btn-tm text-white w-100">Submit</button></div>
        <div class="col-12"><input name="note" class="form-control" placeholder="Note (optional)"></div>
    </form>
</div>
<div class="tm-card p-3">
    <h6>Transaction History</h6>
    <table class="table table-sm">
        <thead><tr><th>Type</th><th>Qty</th><th>Note</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($transactions as $t): ?>
            <tr><td><?= e($t['type']) ?></td><td><?= (int) $t['quantity'] ?></td><td><?= e($t['note']) ?></td><td><?= format_date($t['created_at'], 'd M Y H:i') ?></td></tr>
        <?php endforeach; ?>
        <?php if (empty($transactions)): ?><tr><td colspan="4" class="text-muted text-center">No transactions yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
</div>
</div>
