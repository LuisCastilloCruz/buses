<?php

$host = parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true) || !in_array($host, ['localhost', '127.0.0.1', 'tattoo.localhost', 'dental.localhost', 'comercio.localhost'], true)) {
    http_response_code(403);
    exit;
}
header('X-MultiFactura-Native: ' . hash('sha256', strtolower(str_replace('\\', '/', realpath(dirname(__DIR__))))));
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$public = realpath(dirname(__DIR__) . '/public');
$file = realpath($public . $path);
if ($file && strpos($file, $public . DIRECTORY_SEPARATOR) === 0 && is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
    return false;
}
$app = (require __DIR__ . '/bootstrap.php');
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
