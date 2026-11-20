<?php

namespace Modules\AiReception\Native;

class ModuleMiddleware
{
    public function handle($request, \Closure $next, $module, $level = 'calendar')
    {
        app(Access::class)->require($module, $level);
        return $next($request);
    }
}
