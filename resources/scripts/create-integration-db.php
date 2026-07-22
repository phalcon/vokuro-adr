<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__, 2) . '/tests', '.env.test')->safeLoad();

$host = getenv('DB_HOST') ?: 'mysql';
$port = (int) (getenv('DB_PORT') ?: 3306);
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'secret';

$pdo = new PDO(sprintf('mysql:host=%s;port=%d', $host, $port), $user, $pass);
$pdo->exec(
    'CREATE DATABASE IF NOT EXISTS vokuro_adr_test '
    . 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
);
