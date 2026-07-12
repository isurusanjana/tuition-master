<h4 class="mb-3">Add Inventory Item</h4>
<div class="tm-card p-4">
<form method="POST" action="<?= route('inventory.store') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Item Name *</label><input name="name" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Category</label><input name="category" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">SKU</label><input name="sku" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Opening Quantity</label><input type="number" name="quantity" class="form-control" value="0"></div>
        <div class="col-md-4"><label class="form-label">Unit</label><input name="unit" class="form-control" value="pcs"></div>
        <div class="col-md-4"><label class="form-label">Unit Price</label><input type="number" step="0.01" name="unit_price" class="form-control" value="0"></div>
        <div class="col-md-4"><label class="form-label">Reorder Level</label><input type="number" name="reorder_level" class="form-control" value="0"></div>
        <div class="col-md-4"><label class="form-label">Location</label><input name="location" class="form-control"></div>
    </div>
    <div class="mt-4"><button class="btn btn-tm text-white">Add Item</button></div>
</form>
</div>
