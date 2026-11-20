<?php

namespace Modules\AiReception\Native;

class Access
{
    public function business() : int
    {
        $tenant = app(\Hyn\Tenancy\Environment::class)->tenant();
        if (!$tenant) {
            abort(403, 'Empresa no identificada.');
        }
        if (app('db')->connection('tenant')->getDatabaseName() !== $tenant->uuid) {
            abort(403, 'Contexto de empresa invalido.');
        }
        return (int) $tenant->id;
    }
    public function enabled(string $module) : bool
    {
        $tenant = app(\Hyn\Tenancy\Environment::class)->tenant();
        if (!$tenant) {
            return false;
        }
        $db = app('db')->connection('system');
        if (!$db->getSchemaBuilder()->hasTable('client_module_entitlements')) {
            return false;
        }
        $client = $db->table('client_module_entitlements as e')->join('modules as m', 'm.id', '=', 'e.module_id')->join('clients as c', 'c.id', '=', 'e.client_id')->join('hostnames as h', 'h.id', '=', 'c.hostname_id')->where('h.website_id', $tenant->id)->where('m.value', $module)->where('e.enabled', true)->where('c.locked_tenant', false)->select('c.id','c.plan_id')->first();
        return $client && app(SubscriptionPolicy::class)->allows($client, $module);
    }
    public function allows(string $module, string $level) : bool
    {
        $user = auth('web')->user();
        if (!$user || $user->locked || !$this->enabled($module)) {
            return false;
        }
        return $user->modules()->where('value', $module)->exists() && $user->levels()->where('value', $module . '_' . $level)->exists();
    }
    public function require(string $module, string $level) : void
    {
        $this->business();
        if (!$this->allows($module, $level)) {
            abort(403, 'Modulo o permiso no habilitado para esta empresa y usuario.');
        }
    }
}
