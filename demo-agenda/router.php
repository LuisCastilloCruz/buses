<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (in_array($path, ['/assets/agenda.css', '/assets/agenda.js'], true)) {
    return false;
}
require __DIR__ . '/public/index.php';
