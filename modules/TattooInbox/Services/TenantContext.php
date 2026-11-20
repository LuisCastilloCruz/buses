<?php

namespace Modules\TattooInbox\Services;

use Hyn\Tenancy\Contracts\Tenant;
use Hyn\Tenancy\Contracts\Website;
use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Environment;
use Illuminate\Contracts\Foundation\Application;

/** Hyn 5.3 has no Environment::end(); tenant(null) is a getter, not a reset. */
class TenantContext
{
    private $environment;
    private $connection;
    private $app;

    public function __construct(Environment $environment, Connection $connection, Application $app)
    {
        $this->environment = $environment;
        $this->connection = $connection;
        $this->app = $app;
    }

    public function run(Website $website, callable $operation)
    {
        $previous = $this->environment->tenant();
        $disk = $this->app['config']->get('filesystems.disks.tenant');
        try {
            $this->environment->tenant($website);
            return $operation();
        } finally {
            // Clear stale connection/config even if switching failed. Do not swallow cleanup failures.
            try {
                $this->connection->purge();
            } finally {
                $this->app->singleton(Tenant::class, function () { return null; });
                $this->app['config']->set('filesystems.disks.tenant', $disk);
                if ($this->app->resolved('filesystem')) $this->app['filesystem']->set('tenant', null);
                if ($previous !== null) $this->environment->tenant($previous);
            }
        }
    }
}
