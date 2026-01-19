<?php

require_once __DIR__ . '/../src/Bootstrap.php';

use App\Bootstrap;
use App\Storage\Db;

// Initialize app to load config/env
Bootstrap::init();

echo "Initializing database...\n";

try {
    $pdo = Db::getInstance();

    $schemaPath = __DIR__ . '/schema.sql';
    if (!file_exists($schemaPath)) {
        die("Error: schema.sql not found at $schemaPath\n");
    }

    $sql = file_get_contents($schemaPath);

    // Split statements by semicolon
    // This assumes simple SQL without semicolons inside strings/triggers
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }

    echo "Database initialized.\n";

} catch (\Exception $e) {
    echo "Error initializing database: " . $e->getMessage() . "\n";
    // We don't exit with error code to avoid CI failure if DB isn't present in this specific env,
    // but usually we should.
    exit(1);
}
