<?php

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);
use Illuminate\Support\Facades\Route;

if ($hostname)
{
    Route::domain($hostname->fqdn)->group(function () {

        Route::middleware(['auth:api', 'locked.tenant'])->group(function () {

            // Aquí luego pondremos endpoints API del módulo Tattoo

        });
    });
}