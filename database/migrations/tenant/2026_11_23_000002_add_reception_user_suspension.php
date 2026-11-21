<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class AddReceptionUserSuspension extends Migration {
 public function up(){DB::connection('tenant')->getSchemaBuilder()->table('users',function(Blueprint $t){$t->boolean('aireception_suspended')->default(false);});}
 public function down(){throw new RuntimeException('Explicit permission migration review required.');}
}