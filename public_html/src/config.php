<?php
declare(strict_types=1);

// Настройка MySQL (Beget)
$DB_HOST = 'localhost';
$DB_NAME = 'revengoc_1';
$DB_USER = 'revengoc_1';
$DB_PASS = 'Revengoc_1';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
