<?php
header('Content-Type: text/plain');

if (($_GET['secret'] ?? '') !== 'fajr2026') {
    http_response_code(403);
    die("Access Denied.");
}

$db_host = 'localhost';
$db_name = 'u372267104_fajr';
$db_user = 'u372267104_user';
$db_pass = '&3FV4LU6uB';

try {
    echo "Connecting to remote database...\n";
    $pdo = new PDO("mysql:host=$db_host;charset=utf8", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Recreating database schema...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$db_name`");
    $pdo->exec("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");

    $sqlPath = __DIR__ . '/../database/local_backup.sql';
    if (!file_exists($sqlPath)) {
        die("ERROR: SQL backup file not found at: $sqlPath\n");
    }

    echo "Reading SQL backup file...\n";
    $sql = file_get_contents($sqlPath);

    echo "Executing SQL queries...\n";
    // We execute it using PDO exec
    $pdo->exec($sql);

    echo "DATABASE IMPORT SUCCESSFUL! Cleaned and imported local database to remote server successfully.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
