<?php

use Illuminate\Support\Facades\Route;
Route::middleware(['web', 'auth', 'locked.tenant'])->group(function () {
    $module = 'odontologia';
    Route::post('odontologia/clinical/attachments', '\Modules\Odontologia\Http\Controllers\ClinicalController@upload')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':odontologia,clinical');
    Route::get('odontologia/clinical/attachments/{id}', '\Modules\Odontologia\Http\Controllers\ClinicalController@download')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':odontologia,clinical');
    Route::get('odontologia/clinical', '\Modules\Odontologia\Http\Controllers\ClinicalController@index')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':odontologia,clinical');
    Route::post('odontologia/clinical', '\Modules\Odontologia\Http\Controllers\ClinicalController@store')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':odontologia,clinical');
    require base_path('modules/AiReception/Routes/shared.php');
    Route::get('odontologia', '\\Modules\\Odontologia\\Http\\Controllers\\AdminController@index')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class . ':odontologia,calendar')->name('tenant.odontologia.index');
    Route::post('odontologia/admin/{kind}', '\\Modules\\Odontologia\\Http\\Controllers\\AdminController@store')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class . ':odontologia,manage');
});
