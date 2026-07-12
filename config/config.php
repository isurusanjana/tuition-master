<?php
require_once __DIR__ . '/../core/EnvLoader.php';
EnvLoader::load(__DIR__ . '/../.env');

define('APP_NAME', EnvLoader::get('APP_NAME', 'Tuition Master'));
define('APP_URL', rtrim(EnvLoader::get('APP_URL', 'http://localhost/tuition-master/public'), '/'));
define('APP_DEBUG', EnvLoader::get('APP_DEBUG', 'true') === 'true');
define('APP_KEY', EnvLoader::get('APP_KEY', 'insecure-default-key'));

define('DB_HOST', EnvLoader::get('DB_HOST', '127.0.0.1'));
define('DB_PORT', EnvLoader::get('DB_PORT', '3306'));
define('DB_NAME', EnvLoader::get('DB_NAME', 'tuition_master'));
define('DB_USER', EnvLoader::get('DB_USER', 'root'));
define('DB_PASS', EnvLoader::get('DB_PASS', ''));

define('SESSION_NAME', EnvLoader::get('SESSION_NAME', 'tuitionmaster_session'));
define('UPLOAD_MAX_MB', (int) EnvLoader::get('UPLOAD_MAX_MB', 25));

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CORE_PATH', BASE_PATH . '/core');
define('UPLOAD_PATH', BASE_PATH . '/public/assets/uploads');

error_reporting(APP_DEBUG ? E_ALL : 0);
ini_set('display_errors', APP_DEBUG ? '1' : '0');

date_default_timezone_set('Asia/Colombo');
