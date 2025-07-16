<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = new Application(realpath(__DIR__));

// Load environment
$app->loadEnvironmentFrom('.env');

// Debug database connection
try {
    $app->make('Illuminate\Database\DatabaseManager')
        ->connection()
        ->getPdo();
    echo "✅ Database connected successfully!\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Check if tables exist
try {
    $tables = $app->make('Illuminate\Database\DatabaseManager')
        ->connection()
        ->getSchemaBuilder()
        ->getTableListing();
    echo "✅ Tables found: " . implode(', ', $tables) . "\n";
} catch (Exception $e) {
    echo "❌ Cannot list tables: " . $e->getMessage() . "\n";
}

// Check users table
try {
    $userCount = $app->make('Illuminate\Database\DatabaseManager')
        ->connection()
        ->table('users')
        ->count();
    echo "✅ Users table has {$userCount} records\n";
} catch (Exception $e) {
    echo "❌ Cannot query users table: " . $e->getMessage() . "\n";
}
