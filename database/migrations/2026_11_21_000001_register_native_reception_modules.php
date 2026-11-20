<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class RegisterNativeReceptionModules extends Migration
{
    public function up()
    {
        $db = app('db')->connection('system');
        $s = $db->getSchemaBuilder();
        if (!$s->hasTable('client_module_entitlements')) {
            $s->create('client_module_entitlements', function (Blueprint $t) {
                $t->unsignedInteger('client_id');
                $t->unsignedInteger('module_id');
                $t->boolean('enabled')->default(false);
                $t->timestamp('updated_at')->nullable();
                $t->primary(['client_id', 'module_id'], 'native_client_module_pk');
                $t->foreign('client_id')->references('id')->on('clients');
                $t->foreign('module_id')->references('id')->on('modules');
            });
        }
        Modules\AiReception\Native\Catalog::install($db);
    }
    public function down()
    {
        throw new RuntimeException('Explicit rollback review required; no automatic permission removal.');
    }
}
