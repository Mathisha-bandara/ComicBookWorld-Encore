<?php
/*
 * Comic Book World database connection.
 *
 * Local setup: copy config.local.example.php to config.local.php.
 * Never commit config.local.php or real production credentials.
 */

$databaseConfig = [
    'db_host' => getenv('CBW_DB_HOST') ?: 'localhost',
    'db_name' => getenv('CBW_DB_NAME') ?: 'comic_book_world',
    'db_user' => getenv('CBW_DB_USER') ?: 'root',
    'db_password' => getenv('CBW_DB_PASSWORD') !== false
        ? getenv('CBW_DB_PASSWORD')
        : '',
];

$localConfigPath = dirname(__DIR__) . '/config.local.php';

if (is_file($localConfigPath)) {
    $localConfig = require $localConfigPath;

    if (is_array($localConfig)) {
        $databaseConfig = array_merge($databaseConfig, $localConfig);
    }
}

$servername = (string) ($databaseConfig['db_host'] ?? 'localhost');
$username = (string) ($databaseConfig['db_user'] ?? 'root');
$password = (string) ($databaseConfig['db_password'] ?? '');
$dbname = (string) ($databaseConfig['db_name'] ?? 'comic_book_world');

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
} catch (mysqli_sql_exception $exception) {
    error_log('Comic Book World database connection failed: ' . $exception->getMessage());
    http_response_code(500);
    die('Database connection failed. Check config.local.php and confirm that MySQL is running.');
}

if (!$conn->set_charset('utf8mb4')) {
    error_log('Comic Book World could not set utf8mb4 connection charset.');
}
