<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

$root = dirname(__DIR__);
$envPath = $root.'/.env';

if (! is_file($envPath)) {
    fwrite(STDERR, "Missing .env at {$envPath}\n");
    exit(1);
}

$env = parse_ini_file($envPath, false, INI_SCANNER_RAW);

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$database = $env['DB_DATABASE'] ?? '';
$username = $env['DB_USERNAME'] ?? '';
$password = $env['DB_PASSWORD'] ?? '';

echo "BetZone database check\n";
echo "------------------------\n";
echo "Host     : {$host}\n";
echo "Port     : {$port}\n";
echo "Database : {$database}\n";
echo "Username : {$username}\n\n";

if (! extension_loaded('pdo_mysql')) {
    fwrite(STDERR, "pdo_mysql extension is not installed.\n");
    exit(1);
}

$dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);

    $tables = (int) $pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn();
    $users = 0;

    try {
        $users = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    } catch (Throwable) {
        // Tables may not be imported yet.
    }

    echo "OK: connected successfully.\n";
    echo "Tables: {$tables}\n";
    echo "Users : {$users}\n";

    if ($tables === 0) {
        echo "\nDatabase is empty. Import database/betzone-dump.sql.\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, "FAILED: ".$e->getMessage()."\n\n");
    echo "Hints:\n";
    echo "- 'Connection refused' => MySQL is not running on this host/port. Shared hosting normally uses port 3306.\n";
    echo "- 'Access denied' => DB_USERNAME/DB_PASSWORD are wrong or the user lacks privileges.\n";
    echo "- 'Unknown database' => create the database or fix DB_DATABASE.\n";
    echo "- After editing .env run: php artisan config:clear\n";
    exit(1);
}
