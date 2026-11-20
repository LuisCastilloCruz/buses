<?php

use Illuminate\Database\Migrations\Migration;
class CreateOdontologiaAdminTables extends Migration
{
    public function up()
    {
        Modules\Odontologia\Database\SchemaInstaller::install(app('db')->connection('tenant'));
    }
    public function down()
    {
        throw new RuntimeException('Explicit data archival required.');
    }
}
