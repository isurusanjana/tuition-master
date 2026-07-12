<?php
class InventoryTransaction extends Model
{
    protected string $table = 'inventory_transactions';
    protected bool $tenantScoped = false;
    protected array $fillable = ['item_id','type','quantity','note','created_by'];

    public function record(int $itemId, string $type, int $qty, string $note, int $createdBy): void
    {
        Database::query(
            "INSERT INTO inventory_transactions (item_id, type, quantity, note, created_by) VALUES (:i,:t,:q,:n,:c)",
            ['i' => $itemId, 't' => $type, 'q' => $qty, 'n' => $note, 'c' => $createdBy]
        );
        $delta = $type === 'out' ? -abs($qty) : abs($qty);
        Database::query("UPDATE inventory_items SET quantity = quantity + :d WHERE id = :i", ['d' => $delta, 'i' => $itemId]);
    }

    public function forItem(int $itemId): array
    {
        return Database::fetchAll("SELECT * FROM inventory_transactions WHERE item_id = :i ORDER BY created_at DESC", ['i' => $itemId]);
    }
}
