<?php
// Optional CLI router for automated checks; students use Uniform Server/Apache.
declare(strict_types=1);
$root = __DIR__ . '/public';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$file = realpath($root . $path);
if ($file !== false && str_starts_with($file, $root . DIRECTORY_SEPARATOR) && is_file($file)) {
    return false;
}
require $root . '/index.php';
