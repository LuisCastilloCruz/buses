<?php

require dirname(__DIR__) . '/vendor/autoload.php';
class AgendaLabApplication extends Illuminate\Foundation\Application
{
    public function configPath($path = '')
    {
        return $this->basePath('demo-agenda/config') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
    public function getCachedConfigPath()
    {
        return $this->basePath('.agenda-runtime/cache/config.php');
    }
    public function getCachedServicesPath()
    {
        return $this->basePath('.agenda-runtime/cache/services.php');
    }
    public function getCachedPackagesPath()
    {
        return $this->basePath('.agenda-runtime/cache/packages.php');
    }
    public function getCachedRoutesPath()
    {
        return $this->basePath('.agenda-runtime/cache/routes.php');
    }
}
$app = new AgendaLabApplication(dirname(__DIR__));
$app->useStoragePath(dirname(__DIR__) . '/.agenda-runtime/storage');
$app->useEnvironmentPath(dirname(__DIR__) . '/.agenda-runtime');
$app->loadEnvironmentFrom('.never-load-env');
$app->instance(Illuminate\Foundation\PackageManifest::class, new class(new Illuminate\Filesystem\Filesystem(), $app->basePath(), $app->getCachedPackagesPath()) extends Illuminate\Foundation\PackageManifest
{
    public function providers()
    {
        return [];
    }
    public function aliases()
    {
        return [];
    }
});
$app->singleton(Illuminate\Contracts\Http\Kernel::class, Modules\AiReception\Agenda\Http\Kernel::class);
$app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, Modules\AiReception\Agenda\Http\Handler::class);
return $app;
