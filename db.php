<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function buildPgsqlDsnFromUrl(string $databaseUrl): array
{
    $parts = parse_url($databaseUrl);
    if ($parts === false || !isset($parts['host'])) {
        return [null, null, null];
    }

    $host = $parts['host'];
    $port = isset($parts['port']) ? (string)$parts['port'] : '5432';
    $dbName = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
    $user = isset($parts['user']) ? urldecode($parts['user']) : '';
    $pass = isset($parts['pass']) ? urldecode($parts['pass']) : '';

    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $dbName);

    if (isset($parts['query'])) {
        parse_str($parts['query'], $queryParams);
        if (!empty($queryParams['sslmode'])) {
            $dsn .= ';sslmode=' . $queryParams['sslmode'];
        }
    }

    return [$dsn, $user, $pass];
}

function getDb(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', db_host(), db_port(), db_name());
    $user = db_user();
    $pass = db_pass();

    $databaseUrl = database_url();
    if ($databaseUrl !== '') {
        [$urlDsn, $urlUser, $urlPass] = buildPgsqlDsnFromUrl($databaseUrl);
        if ($urlDsn !== null) {
            $dsn = $urlDsn;
            $user = $urlUser ?? $user;
            $pass = $urlPass ?? $pass;
        }
    }

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}