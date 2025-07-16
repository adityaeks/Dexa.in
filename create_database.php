<?php

require_once 'vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Connect to MySQL without database
    $pdo = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';port=' . $_ENV['DB_PORT'],
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Create database
    $dbName = $_ENV['DB_DATABASE'];
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    echo "✅ Database '{$dbName}' created successfully!\n";

} catch (Exception $e) {
    echo "❌ Error creating database: " . $e->getMessage() . "\n";
}
