<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'stage' => require dirname(__DIR__) . '/stage.php',
    'php' => PHP_VERSION,
    'sapi' => PHP_SAPI,
    'extensions' => array_combine(
        ['mbstring', 'pdo_sqlite', 'openssl', 'dom', 'xmlwriter'],
        array_map('extension_loaded', ['mbstring', 'pdo_sqlite', 'openssl', 'dom', 'xmlwriter'])
    ),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
