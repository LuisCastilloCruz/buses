<?php

namespace Modules\AiReception\Native;

class TenantSession
{
    public function handle($request, \Closure $next)
    {
        $tenant = app(\Hyn\Tenancy\Environment::class)->tenant();
        $context = $tenant ? 'tenant:' . $tenant->id : 'system';
        $bound = $request->session()->get('native_tenant_context');
        if ($bound === null && !$request->is('login') && (auth('web')->check() || auth('admin')->check())) {
            $request->session()->invalidate();
            auth('web')->logout();
            auth('admin')->logout();
            abort(401, 'Inicia sesion nuevamente para vincular la empresa.');
        }
        if ($bound !== null && $bound !== $context) {
            abort(403, 'La sesion pertenece a otra empresa.');
        }
        $response = $next($request);
        if (auth('web')->check() || auth('admin')->check()) {
            $request->session()->put('native_tenant_context', $context);
        }
        return $response;
    }
}
