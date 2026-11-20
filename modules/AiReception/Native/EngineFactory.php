<?php

namespace Modules\AiReception\Native;

class EngineFactory
{
    public function make(string $module) : \Modules\AiReception\Agenda\BookingService
    {
        if (!in_array($module, ['aireception', 'odontologia'], true)) {
            abort(403);
        }
        app(Access::class)->business();
        $connection = app('db')->connection('tenant');
        if ($connection->transactionLevel() !== 0) {
            throw new \LogicException('Create agenda engine before starting a transaction.');
        }
        $connection->statement('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        $adapters = [];
        foreach (['tattoo', 'dental'] as $domain) {
            if (app()->bound('aireception.domain.' . $domain)) {
                $adapters[$domain] = app('aireception.domain.' . $domain);
            }
        }
        return new \Modules\AiReception\Agenda\BookingService(app('db')->connection('tenant'), new Authority($module), new CalendarGuard($adapters));
    }
}
