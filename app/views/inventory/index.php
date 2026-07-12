<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Inventory</h4>
    <?php if (Permission::can('inventory','add')): ?>
    <a href="<?= route('inventory.create') ?>" class="btn btn-tm text-white"><i class="bi bi-plus-lg"></i> Add Item</a>
    <?php endif; ?>
</div>
<div class="tm-card p-3">
<table class="table table-hover datatable">
    <thead><tr><th>Name</th><th>Category</th><th>SKU</th><th>Qty</th><th>Unit Price</th><th>Reorder Level</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($items as $i): ?>
        <tr class="<?= $i['quantity'] <= $i['reorder_level'] ? 'table-warning' : '' ?>">
            <td><?= e($i['name']) ?></td>
            <td><?= e($i['category']) ?></td>
            <td><?= e($i['sku']) ?></td>
            <td><?= (int) $i['quantity'] ?> <?= e($i['unit']) ?></td>
            <td><?= number_format($i['unit_price'],2) ?></td>
            <td><?= (int) $i['reorder_level'] ?></td>
            <td class="text-end">
                <?php if (Permission::can('inventory','edit')): ?><a href="<?= route('inventory.edit', ['id' => $i['id']]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a><?php endif; ?>
                <?php if (Permission::can('inventory','delete')): ?>
                <form class="d-inline" method="POST" action="<?= route('inventory.delete', ['id' => $i['id']]) ?>" data-confirm="Delete this item?">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
