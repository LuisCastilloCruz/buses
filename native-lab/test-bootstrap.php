<?php

$host = $argv[1] ?? 'dental';
if (!in_array($host, ['tattoo', 'dental', 'comercio', 'cuarta'], true)) {
    throw new RuntimeException('Unknown fixture host');
}
$_SERVER['HTTP_HOST'] = $host . '.localhost:8876';
$_SERVER['SERVER_NAME'] = $host . '.localhost';
$_SERVER['SERVER_PORT'] = 8876;
$_SERVER['REQUEST_URI'] = '/aireception/calendar';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$app = (require __DIR__ . '/bootstrap.php');
$app->instance('request', Illuminate\Http\Request::capture());
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
$hostname = \Hyn\Tenancy\Models\Hostname::where('fqdn', $host . '.localhost')->firstOrFail();
$environment = app(\Hyn\Tenancy\Environment::class);
$environment->hostname($hostname);
$environment->tenant($hostname->website);
$db = app('db')->connection('tenant');
$meta = $db->selectOne('SELECT @@port port,@@datadir dir,DATABASE() db');
$expected = ['tattoo' => 'mf_v4_tattoo', 'dental' => 'mf_v4_dental', 'comercio' => 'mf_v4_commerce', 'cuarta'=>'mf_v4_cuarta'][$host];
if ((int) $meta->port !== 13319 || $meta->db !== $expected || strtolower(str_replace('\\', '/', $meta->dir)) !== strtolower(str_replace('\\', '/', realpath(dirname(__DIR__))) . '/.native-runtime/mysql-data/')) {
    throw new RuntimeException('Unsafe test database');
}
$actor = (int) ($argv[2] ?? 1);
auth('web')->setUser(App\Models\Tenant\User::findOrFail($actor));
$business = app(Modules\AiReception\Native\Access::class)->business();
$engine = app(Modules\AiReception\Native\EngineFactory::class)->make('aireception');
return $app;
