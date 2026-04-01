<?php

$dbHost = 'localhost';
$dbName = 'bigbrothers';
$dbUser = 'root';
$dbPass = '';
$dbCharset = 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Please try again later.');
}
