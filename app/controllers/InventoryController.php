<?php
class InventoryController extends Controller
{
    public function index(): void
    {
        $this->authorize('inventory', 'view');
        $items = (new InventoryItem())->all([], 'name');
        $this->view('inventory/index', ['title' => 'Inventory', 'items' => $items]);
    }

    public function create(): void
    {
        $this->authorize('inventory', 'add');
        $this->view('inventory/create', ['title' => 'Add Inventory Item']);
    }

    public function store(): void
    {
        $this->authorize('inventory', 'add');
        $this->validateCsrf();
        $data = Request::only(['name','category','sku','quantity','unit','unit_price','reorder_level','location']);
        $v = new Validator($data);
        $v->required('name')->numeric('quantity')->numeric('unit_price');
        if ($v->fails()) redirect_with_error('/inventory/create', $v->firstError());

        (new InventoryItem())->create($data + ['status' => 'active', 'created_by' => Auth::id()]);
        log_activity('create', 'inventory', "Added inventory item: {$data['name']}");
        redirect_with_success('/inventory', 'Inventory item added.');
    }

    public function edit(int $id): void
    {
        $this->authorize('inventory', 'edit');
        $item = (new InventoryItem())->find($id);
        if (!$item) Response::redirect('/inventory');
        $transactions = (new InventoryTransaction())->forItem($id);
        $this->view('inventory/edit', ['title' => 'Edit Item', 'item' => $item, 'transactions' => $transactions]);
    }

    public function update(int $id): void
    {
        $this->authorize('inventory', 'edit');
        $this->validateCsrf();
        $data = Request::only(['name','category','sku','unit','unit_price','reorder_level','location','status']);
        (new InventoryItem())->update($id, $data);
        redirect_with_success('/inventory', 'Inventory item updated.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('inventory', 'delete');
        $this->validateCsrf();
        (new InventoryItem())->delete($id);
        redirect_with_success('/inventory', 'Inventory item deleted.');
    }

    public function transaction(int $id): void
    {
        $this->authorize('inventory', 'edit');
        $this->validateCsrf();
        $type = Request::input('type');
        $qty = (int) Request::input('quantity');
        $note = Request::input('note', '');
        if ($qty > 0) {
            (new InventoryTransaction())->record($id, $type, $qty, $note, Auth::id());
        }
        redirect_with_success("/inventory/$id/edit", 'Stock transaction recorded.');
    }
}
