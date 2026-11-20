<?php

namespace Modules\AiReception\Agenda\Http;

class LocalOnly
{
    public function handle($request, \Closure $next)
    {
        if (!in_array($request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true) || !in_array($request->getHost(), ['127.0.0.1', 'localhost'], true)) {
            abort(403);
        }
        $response = $next($request);
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self'; connect-src 'self'; img-src 'self' data:; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Cache-Control', 'no-store');
        $response->headers->set('X-Agenda-Lab', hash('sha256', strtolower(str_replace('\\', '/', realpath(dirname(__DIR__, 4))))));
        return $response;
    }
}
