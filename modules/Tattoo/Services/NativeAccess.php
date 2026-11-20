<?php

namespace Modules\Tattoo\Services;

class NativeAccess
{
    public function handle($request, \Closure $next)
    {
        if (config('aireception.native_enabled', false)) {
            app(\Modules\AiReception\Native\Access::class)->business();
            $user = auth('web')->user();
            if (!$user) {
                abort(401);
            }
            if (!$user->modules()->where('value', 'tattoo')->exists()) {
                abort(403);
            }
            $level = str_replace('-', '_', $request->segment(1));
            if (!$user->levels()->where('value', $level)->exists()) {
                abort(403);
            }
        }
        return $next($request);
    }
}
