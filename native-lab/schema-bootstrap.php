<?php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = new Illuminate\Foundation\Application(dirname(__DIR__));
$s = json_decode(file_get_contents(dirname(__DIR__) . '/.native-runtime/settings.json'), true);
if ($s['root'] !== str_replace('\\', '/', realpath(dirname(__DIR__))) || $s['purpose'] !== 'multifactura-native-v6') {
    throw new RuntimeException('Invalid lab root.');
}
$name = $argv[1] ?? 'mf_v6_system';
if (!in_array($name, ['mf_v6_system', 'mf_v6_tattoo', 'mf_v6_dental', 'mf_v6_commerce', 'mf_v6_cuarta'], true)) {
    throw new RuntimeException('Unknown database.');
}
$c = ['driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 13321, 'database' => $name, 'username' => $s['user'], 'password' => $s['password'], 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => false, 'engine' => 'InnoDB'];
$system = $c;
$system['database'] = 'mf_v6_system';
if ($name === 'mf_v6_cuarta') {
    $reservation=json_decode(file_get_contents(dirname(__DIR__).'/.native-runtime/provision.json'),true);
    $c['username']='mf_v6_cuarta';$c['password']=$reservation['password'];
}
$app->instance('config', new Illuminate\Config\Repository(['database' => ['default' => $name === 'mf_v6_system' ? 'system' : 'tenant', 'connections' => ['system' => $system, 'tenant' => $c]], 'app' => ['key' => $s['key'], 'timezone' => 'America/Lima'], 'tenant' => ['app_url_base' => 'localhost'], 'tenancy' => ['db' => ['system-connection-name' => 'system', 'tenant-connection-name' => 'tenant']]]));
$app['config']->set('cache', ['default' => 'array', 'stores' => ['array' => ['driver' => 'array']]]);
$app->instance('request', Illuminate\Http\Request::create('http://localhost:8878'));
$app->singleton(Illuminate\Contracts\Console\Kernel::class, Illuminate\Foundation\Console\Kernel::class);
$app->bind(Hyn\Tenancy\Contracts\Database\PasswordGenerator::class, Hyn\Tenancy\Generators\Database\DefaultPasswordGenerator::class);
Illuminate\Support\Facades\Facade::setFacadeApplication($app);
Illuminate\Foundation\AliasLoader::getInstance(['DB' => Illuminate\Support\Facades\DB::class, 'Schema' => Illuminate\Support\Facades\Schema::class, 'Config' => Illuminate\Support\Facades\Config::class])->register();
foreach ([Illuminate\Events\EventServiceProvider::class, Illuminate\Database\DatabaseServiceProvider::class, Illuminate\Filesystem\FilesystemServiceProvider::class, Illuminate\Routing\RoutingServiceProvider::class, Illuminate\Cache\CacheServiceProvider::class] as $provider) {
    $app->register($provider);
}
$app->boot();
$db = $app['db']->connection();
$row = $db->selectOne('SELECT @@port port,@@datadir datadir,DATABASE() name');
if ((int) $row->port !== 13321 || $row->name !== $name || strtolower(str_replace('\\', '/', $row->datadir)) !== strtolower($s['root'] . '/.native-runtime/mysql-data/')) {
    throw new RuntimeException('Unsafe database.');
}
return $app;
