<?php
// Роутер для встроенного сервера PHP: статику отдаёт сам сервер (с правильным
// Content-Type), всё остальное уходит в Symfony.
// Запуск: php -S 127.0.0.1:8100 -t public dev-router.php
$docroot = $_SERVER['DOCUMENT_ROOT'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath($docroot . $path);

if ($file !== false && is_file($file) && str_starts_with($file, realpath($docroot)) && basename($file) !== 'index.php') {
    return false;
}

$_SERVER['SCRIPT_FILENAME'] = $docroot . '/index.php';
require $_SERVER['SCRIPT_FILENAME'];
