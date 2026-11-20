<?php

use Illuminate\Database\Migrations\Migration;
class RegisterNativeReceptionCatalog extends Migration
{
    public function up()
    {
        Modules\AiReception\Native\Catalog::install(app('db')->connection('tenant'));
    }
    public function down()
    {
        throw new RuntimeException('No automatic removal of module catalogs.');
    }
}
