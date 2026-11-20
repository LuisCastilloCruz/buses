<?php

use Illuminate\Database\Migrations\Migration;
class CreateSharedReceptionAgenda extends Migration
{
    public function up()
    {
        Modules\AiReception\Native\SchemaInstaller::install(app('db')->connection('tenant'));
    }
    public function down()
    {
        throw new RuntimeException('Reservations require explicit archival before rollback.');
    }
}
