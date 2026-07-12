<?php
class InventoryItem extends Model
{
    protected string $table = 'inventory_items';
    protected array $fillable = ['tuition_center_id','name','category','sku','quantity','unit','unit_price','reorder_level','location','status','created_by'];
}
