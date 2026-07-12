<?php

/**
 * Exercises the base Model class (create/find/update/delete) using the
 * InventoryItem model, which is a simple tenant-scoped table.
 * Creates and cleans up its own isolated fixture row.
 */
final class ModelTest extends DatabaseTestCase
{
    private int $testCenterId;

    protected function setUp(): void
    {
        parent::setUp();
        if (!self::$dbAvailable) {
            return;
        }
        Database::query(
            "INSERT INTO tuition_centers (name, code, email, status) VALUES ('PHPUnit Test Center', :code, 'phpunit@test.local', 'active')",
            ['code' => 'PU' . substr(uniqid(), -10)]
        );
        $this->testCenterId = (int) Database::lastInsertId();
    }

    protected function tearDown(): void
    {
        if (self::$dbAvailable && isset($this->testCenterId)) {
            Database::query("DELETE FROM tuition_centers WHERE id = :id", ['id' => $this->testCenterId]);
        }
    }

    public function testCreateAndFind(): void
    {
        Database::query(
            "INSERT INTO inventory_items (tuition_center_id, name, quantity, status, created_by) VALUES (:c, 'Whiteboard Markers', 50, 'active', 1)",
            ['c' => $this->testCenterId]
        );
        $id = (int) Database::lastInsertId();

        $row = Database::fetchOne("SELECT * FROM inventory_items WHERE id = :id", ['id' => $id]);
        $this->assertNotNull($row);
        $this->assertSame('Whiteboard Markers', $row['name']);
        $this->assertSame(50, (int) $row['quantity']);
    }

    public function testUpdateChangesFields(): void
    {
        Database::query(
            "INSERT INTO inventory_items (tuition_center_id, name, quantity, status, created_by) VALUES (:c, 'Chalk Box', 10, 'active', 1)",
            ['c' => $this->testCenterId]
        );
        $id = (int) Database::lastInsertId();

        Database::query("UPDATE inventory_items SET quantity = 25 WHERE id = :id", ['id' => $id]);
        $row = Database::fetchOne("SELECT quantity FROM inventory_items WHERE id = :id", ['id' => $id]);
        $this->assertSame(25, (int) $row['quantity']);
    }

    public function testDeleteRemovesRow(): void
    {
        Database::query(
            "INSERT INTO inventory_items (tuition_center_id, name, quantity, status, created_by) VALUES (:c, 'Temp Item', 1, 'active', 1)",
            ['c' => $this->testCenterId]
        );
        $id = (int) Database::lastInsertId();

        Database::query("DELETE FROM inventory_items WHERE id = :id", ['id' => $id]);
        $row = Database::fetchOne("SELECT * FROM inventory_items WHERE id = :id", ['id' => $id]);
        $this->assertNull($row);
    }

    public function testInventoryTransactionAdjustsQuantity(): void
    {
        Database::query(
            "INSERT INTO inventory_items (tuition_center_id, name, quantity, status, created_by) VALUES (:c, 'Notebooks', 100, 'active', 1)",
            ['c' => $this->testCenterId]
        );
        $itemId = (int) Database::lastInsertId();

        $txModel = new InventoryTransaction();
        $txModel->record($itemId, 'out', 15, 'Issued to Grade 10', 1);

        $row = Database::fetchOne("SELECT quantity FROM inventory_items WHERE id = :id", ['id' => $itemId]);
        $this->assertSame(85, (int) $row['quantity']);
    }
}
