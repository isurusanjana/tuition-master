<?php

/**
 * Exercises Auth::attempt(), tenant scoping, and the user-hierarchy
 * permission checks (Auth::canManageUser / Auth::subordinateIds) that
 * enforce "a user can only manage users under them" data isolation.
 */
final class AuthTest extends DatabaseTestCase
{
    private int $centerId;
    private int $adminRoleId;
    private int $teacherRoleId;
    private int $studentRoleId;
    private array $createdUserIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        if (!self::$dbAvailable) {
            return;
        }
        Session::start();

        Database::query(
            "INSERT INTO tuition_centers (name, code, email, status) VALUES ('AuthTest Center', :code, 'authtest@test.local', 'active')",
            ['code' => 'AU' . substr(uniqid(), -10)]
        );
        $this->centerId = (int) Database::lastInsertId();

        $this->adminRoleId = (int) Database::fetchOne("SELECT id FROM roles WHERE slug = 'center_admin'")['id'];
        $this->teacherRoleId = (int) Database::fetchOne("SELECT id FROM roles WHERE slug = 'teacher'")['id'];
        $this->studentRoleId = (int) Database::fetchOne("SELECT id FROM roles WHERE slug = 'student'")['id'];
    }

    protected function tearDown(): void
    {
        if (self::$dbAvailable) {
            Auth::logout();
            foreach ($this->createdUserIds as $id) {
                Database::query("DELETE FROM users WHERE id = :id", ['id' => $id]);
            }
            Database::query("DELETE FROM tuition_centers WHERE id = :id", ['id' => $this->centerId]);
        }
    }

    private function createUser(string $username, string $password, int $roleId, ?int $parentId = null): int
    {
        Database::query(
            "INSERT INTO users (tuition_center_id, role_id, parent_user_id, first_name, last_name, email, username, password, status)
             VALUES (:c, :r, :p, 'Test', 'User', :email, :u, :pw, 'active')",
            [
                'c' => $this->centerId, 'r' => $roleId, 'p' => $parentId,
                'email' => $username . '@authtest.local', 'u' => $username,
                'pw' => password_hash($password, PASSWORD_BCRYPT),
            ]
        );
        $id = (int) Database::lastInsertId();
        $this->createdUserIds[] = $id;
        return $id;
    }

    public function testAttemptFailsWithWrongPassword(): void
    {
        $this->createUser('authtest_wrongpw', 'CorrectPass1', $this->adminRoleId);
        $this->assertFalse(Auth::attempt('authtest_wrongpw', 'WrongPassword'));
    }

    public function testAttemptSucceedsWithCorrectCredentials(): void
    {
        $this->createUser('authtest_correct', 'CorrectPass1', $this->adminRoleId);
        $this->assertTrue(Auth::attempt('authtest_correct', 'CorrectPass1'));
        $this->assertTrue(Auth::check());
        $this->assertSame('center_admin', Auth::roleSlug());
        $this->assertSame($this->centerId, Auth::centerId());
    }

    public function testCanManageUserWithinHierarchy(): void
    {
        $adminId = $this->createUser('authtest_admin', 'Pass12345', $this->adminRoleId);
        $teacherId = $this->createUser('authtest_teacher', 'Pass12345', $this->teacherRoleId, $adminId);
        $studentId = $this->createUser('authtest_student', 'Pass12345', $this->studentRoleId, $teacherId);

        Auth::attempt('authtest_admin', 'Pass12345');

        $teacher = Database::fetchOne(
            "SELECT u.*, r.slug as role_slug, r.level as role_level FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id = :id",
            ['id' => $teacherId]
        );
        $student = Database::fetchOne(
            "SELECT u.*, r.slug as role_slug, r.level as role_level FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id = :id",
            ['id' => $studentId]
        );

        // The admin created both the teacher and (transitively) the student, so should be able to manage both.
        $this->assertTrue(Auth::canManageUser($teacher));
        $this->assertTrue(Auth::canManageUser($student));

        $subordinates = Auth::subordinateIds($adminId);
        $this->assertContains($teacherId, $subordinates);
        $this->assertContains($studentId, $subordinates);
    }

    public function testCannotManageUserOutsideHierarchy(): void
    {
        $adminId = $this->createUser('authtest_admin2', 'Pass12345', $this->adminRoleId);
        // a teacher created directly (no parent), simulating a user outside the admin's own subtree
        $unrelatedTeacherId = $this->createUser('authtest_unrelated', 'Pass12345', $this->teacherRoleId, null);

        Auth::attempt('authtest_admin2', 'Pass12345');

        $unrelated = Database::fetchOne(
            "SELECT u.*, r.slug as role_slug, r.level as role_level FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id = :id",
            ['id' => $unrelatedTeacherId]
        );

        $this->assertFalse(Auth::canManageUser($unrelated));
    }
}
