<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__, 2) . '/tests', '.env.test')->safeLoad();

/**
 * Real environment variables (the container's `env_file`) win; the `.env.test`
 * values loaded into `$_ENV` cover CI, where nothing is exported to the shell.
 */
$env = static function (string $key, string $default): string {
    $value = getenv($key);

    if (false !== $value) {
        return $value;
    }

    return (string) ($_ENV[$key] ?? $default);
};

$host = $env('DB_HOST', 'mysql');
$port = (int) $env('DB_PORT', '3306');
$user = $env('DB_USERNAME', 'root');
$pass = $env('DB_PASSWORD', 'secret');

$pdo = new PDO(sprintf('mysql:host=%s;port=%d', $host, $port), $user, $pass);
$pdo->exec(
    'CREATE DATABASE IF NOT EXISTS vokuro_adr_test '
    . 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
);
