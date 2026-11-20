<?php
use Illuminate\Database\Migrations\Migration;
class RegisterClinicalPermission extends Migration {
    public function up(){Modules\AiReception\Native\Catalog::install(DB::connection('system'));}
    public function down(){throw new RuntimeException('Permission removal requires review.');}
}
