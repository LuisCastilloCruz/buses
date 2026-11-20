<?php

namespace Modules\AiReception\Agenda\Http;

class Kernel extends \Illuminate\Foundation\Http\Kernel
{
    protected $middleware = [LocalOnly::class];
    protected $middlewareGroups = ['web' => [\Illuminate\Cookie\Middleware\EncryptCookies::class, \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, \Illuminate\Session\Middleware\StartSession::class, \Illuminate\View\Middleware\ShareErrorsFromSession::class, \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]];
}
