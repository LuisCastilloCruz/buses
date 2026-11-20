<?php
/** Lab-only Hyn adapter. Application never receives CREATE USER or global grants. */
class NativeReservedPassword extends \Hyn\Tenancy\Generators\Database\DefaultPasswordGenerator
{
    public function generate(\Hyn\Tenancy\Contracts\Website $website): string
    {
        if ($website->uuid === 'mf_v4_cuarta') {
            $reservation=json_decode(file_get_contents(__DIR__.'/../.native-runtime/provision.json'),true);
            return $reservation['password'];
        }
        return parent::generate($website);
    }
}
class NativeReservedDatabase extends \Hyn\Tenancy\Generators\Webserver\Database\Drivers\MariaDB
{
    public function created(\Hyn\Tenancy\Events\Websites\Created $event, array $config, \Hyn\Tenancy\Database\Connection $connection): bool
    {
        if (!config('native_lab.enabled') || $event->website->uuid !== 'mf_v4_cuarta' || $config['database'] !== 'mf_v4_cuarta' || $config['host'] !== '127.0.0.1' || (int)$config['port'] !== 13319) throw new \RuntimeException('No disposable database reservation.');
        $p=new \PDO('mysql:host=127.0.0.1;port=13319;dbname=mf_v4_cuarta',$config['username'],$config['password']);
        $r=$p->query('SELECT @@datadir')->fetchColumn();
        if (strtolower(str_replace('\\','/',$r)) !== strtolower(str_replace('\\','/',realpath(__DIR__.'/..')).'/.native-runtime/mysql-data/')) throw new \RuntimeException('Unsafe database.');
        return true;
    }
    public function deleted(\Hyn\Tenancy\Events\Websites\Deleted $event,array $config,\Hyn\Tenancy\Database\Connection $connection): bool
    { throw new \RuntimeException('Laboratory database deletion disabled. Inspect provisioning failure.'); }
}
