<?php
// Dedicated local load front controller; no static-file or arbitrary PHP execution.
$host=parse_url('http://'.($_SERVER['HTTP_HOST']??''),PHP_URL_HOST);
if (!in_array($_SERVER['REMOTE_ADDR']??'', ['127.0.0.1','::1'],true) || !in_array($host,['localhost','127.0.0.1','tattoo.localhost','dental.localhost','comercio.localhost','cuarta.localhost'],true)) {
 http_response_code(403);exit;
}
header('X-MultiFactura-Native: '.hash('sha256',strtolower(str_replace('\\','/',realpath(dirname(__DIR__))))));
header('X-MultiFactura-Pool: v6-four-workers');
$app=require __DIR__.'/bootstrap.php';
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$request=Illuminate\Http\Request::capture();
$response=$kernel->handle($request);$response->send();$kernel->terminate($request,$response);
