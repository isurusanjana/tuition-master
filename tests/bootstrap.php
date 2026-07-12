<?php
/**
 * PHPUnit bootstrap.
 * Loads the application config/autoloader so tests can use core classes and models.
 * DB-dependent tests will gracefully skip if no test database connection is available
 * (see tests/DatabaseTestCase.php and README.md "Running Tests" section).
 */
require_once __DIR__ . '/../config/config.php';

spl_autoload_register(function ($class) {
    $paths = [
        CORE_PATH . "/$class.php",
        APP_PATH . "/models/$class.php",
        __DIR__ . "/$class.php",
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

require_once CORE_PATH . '/Helpers.php';
