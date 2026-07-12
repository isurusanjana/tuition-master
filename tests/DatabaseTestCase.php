<?php

use PHPUnit\Framework\TestCase;

/**
 * DatabaseTestCase - base class for tests that require a live MySQL connection.
 * Tests extending this class are automatically skipped if the configured
 * database is unreachable, so `composer test` still runs the pure-logic
 * tests (Validator, Helpers, etc.) in environments without MySQL configured.
 */
abstract class DatabaseTestCase extends TestCase
{
    protected static bool $dbAvailable = false;

    public static function setUpBeforeClass(): void
    {
        try {
            Database::connect()->query('SELECT 1');
            self::$dbAvailable = true;
        } catch (Throwable $e) {
            self::$dbAvailable = false;
        }
    }

    protected function setUp(): void
    {
        if (!self::$dbAvailable) {
            $this->markTestSkipped('Database connection not available - skipping DB-dependent test. See README "Running Tests".');
        }
    }
}
