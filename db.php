<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function getDb(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', db_host(), db_port(), db_name());

    $pdo = new PDO($dsn, db_user(), db_pass(), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}