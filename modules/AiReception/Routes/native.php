<?php

use Illuminate\Support\Facades\Route;
Route::middleware(['web', 'auth', 'locked.tenant'])->group(function () {
    foreach (['aireception'] as $module) {
        require __DIR__ . '/shared.php';
    }
});
