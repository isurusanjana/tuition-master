<?php

/**
 * Exercises Permission::can() resolution order: user-level override
 * takes precedence over the role's default permission set.
 */
final class PermissionTest extends DatabaseTestCase
{
    private int $centerId;
    private int $userId;
    private int $teacherRoleId;
    private string $username;

    protected function setUp(): void
    {
        parent::setUp();
        if (!self::$dbAvailable) {
            return;
        }
        Session::start();

        Database::query(
            "INSERT INTO tuition_centers (name, code, email, status) VALUES ('PermTest Center', :code, 'permtest@test.local', 'active')",
            ['code' => 'PM' . substr(uniqid(), -10)]
        );
        $this->centerId = (int) Database::lastInsertId();
        $this->teacherRoleId = (int) Database::fetchOne("SELECT id FROM roles WHERE slug = 'teacher'")['id'];

        $uniq = substr(uniqid(), -8);
        $this->username = 'permtest_' . $uniq;
        Database::query(
            "INSERT INTO users (tuition_center_id, role_id, first_name, last_name, email, username, password, status)
             VALUES (:c, :r, 'Perm', 'Test', :email, :u, :pw, 'active')",
            ['c' => $this->centerId, 'r' => $this->teacherRoleId, 'email' => $this->username . '@test.local', 'u' => $this->username, 'pw' => password_hash('Pass12345', PASSWORD_BCRYPT)]
        );
        $this->userId = (int) Database::lastInsertId();
        Auth::attempt($this->username, 'Pass12345');
    }

    protected function tearDown(): void
    {
        if (self::$dbAvailable) {
            Database::query("DELETE FROM user_permission WHERE user_id = :u", ['u' => $this->userId]);
            Auth::logout();
            Database::query("DELETE FROM users WHERE id = :id", ['id' => $this->userId]);
            Database::query("DELETE FROM tuition_centers WHERE id = :id", ['id' => $this->centerId]);
        }
    }

    public function testRoleDefaultPermissionGrantsAccess(): void
    {
        // Teachers get 'exams' 'add' by default per seed data.
        $this->assertTrue(Permission::can('exams', 'add'));
    }

    public function testRoleDefaultDeniesUnassignedPermission(): void
    {
        // Teachers do not get 'payroll' 'add' by default.
        $this->assertFalse(Permission::can('payroll', 'add'));
    }

    public function testUserLevelOverrideGrantsExtraPermission(): void
    {
        $this->assertFalse(Permission::can('payroll', 'view'));

        $permId = Database::fetchOne("SELECT id FROM permissions WHERE module_key='payroll' AND action='view'")['id'];
        Database::query("INSERT INTO user_permission (user_id, permission_id, allowed) VALUES (:u,:p,1)", ['u' => $this->userId, 'p' => $permId]);
        Permission::clearCache();

        $this->assertTrue(Permission::can('payroll', 'view'));
    }

    public function testUserLevelOverrideRevokesRoleDefaultPermission(): void
    {
        $this->assertTrue(Permission::can('exams', 'add'));

        $permId = Database::fetchOne("SELECT id FROM permissions WHERE module_key='exams' AND action='add'")['id'];
        Database::query("INSERT INTO user_permission (user_id, permission_id, allowed) VALUES (:u,:p,0)", ['u' => $this->userId, 'p' => $permId]);
        Permission::clearCache();

        $this->assertFalse(Permission::can('exams', 'add'));
    }
}
