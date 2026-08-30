<?php

/**
 * Beyond Barista Academy — Database Migration & Seeder Runner
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/Helpers/helpers.php';

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) === 0) {
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }

    $seederPrefix = 'Database\\Seeders\\';
    $seederDir = BASE_PATH . '/database/seeders/';
    if (strncmp($seederPrefix, $class, strlen($seederPrefix)) === 0) {
        $rel = substr($class, strlen($seederPrefix));
        $file = $seederDir . str_replace('\\', '/', $rel) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    echo "<pre style='background:#1E293B;color:#F8F9FA;padding:24px;border-radius:8px;font-family:monospace;font-size:14px;line-height:1.6;'>";
}

echo "========================================================\n";
echo "   BEYOND BARISTA ACADEMY — DATABASE MIGRATOR & SEEDER\n";
echo "========================================================\n\n";

try {
    $dbConfig = config('database.connections.mysql');
    
    // Connect to MySQL server (without selecting DB first in case it needs to be created)
    $dsnNoDb = sprintf('mysql:host=%s;port=%s;charset=%s', $dbConfig['host'], $dbConfig['port'], $dbConfig['charset']);
    $pdoRoot = new PDO($dsnNoDb, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $dbName = $dbConfig['database'];
    $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database `{$dbName}` checked/created.\n";

    // Read and run schema.sql
    $schemaFile = BASE_PATH . '/database/migrations/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new RuntimeException("Schema file not found: {$schemaFile}");
    }

    $sql = file_get_contents($schemaFile);
    \App\Core\Database::connect()->exec($sql);
    echo "✓ Database schema tables successfully migrated.\n\n";

    // Run database seeder
    \Database\Seeders\DatabaseSeeder::run();

    // Run full BBA course catalog seeder (class plan data)
    \Database\Seeders\BbaCoursesSeeder::run();

    echo "\n✓ System is fully initialized and ready for use!\n";
    echo "  - Super Admin: admin@beyondbarista.rw | Password: Admin@2026\n";
    echo "  - Instructor: instructor@beyondbarista.rw | Password: Instructor@2026\n";
    echo "  - Student: student@beyondbarista.rw | Password: Student@2026\n";

} catch (Throwable $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
}

if (!$isCli) {
    echo "</pre>";
}
