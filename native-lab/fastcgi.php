<?php
// Dedicated local load front controller; no static-file or arbitrary PHP execution.
$host=parse_url('http://'.($_SERVER['HTTP_HOST']??''),PHP_URL_HOST);
if (!in_array($_SERVER['REMOTE_ADDR']??'', ['127.0.0.1','::1'],true) || !in_array($host,['localhost','127.0.0.1','tattoo.localhost','dental.localhost','comercio.localhost','cuarta.localhost'],true)) {
 http_response_code(403);exit;
}
header('X-MultiFactura-Native: '.hash('sha256',strtolower(str_replace('\\','/',realpath(dirname(__DIR__))))));
$pool=$_SERVER['AR_LAB_POOL']??'v6-four-workers';
if(!in_array($pool,['v6-four-workers','v6-4-workers','v6-8-workers'],true)){http_response_code(403);exit;}
header('X-MultiFactura-Pool: '.$pool);
header('X-MultiFactura-Opcode-Cache: '.(function_exists('opcache_get_status') && opcache_get_status(false)!==false ? 'enabled' : 'disabled'));
$app=require __DIR__.'/bootstrap.php';
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$request=Illuminate\Http\Request::capture();
$response=$kernel->handle($request);$response->send();$kernel->terminate($request,$response);
