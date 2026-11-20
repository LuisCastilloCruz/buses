<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionReminderPolicy extends Migration {
 public function up(){DB::connection('tenant')->getSchemaBuilder()->create('aireception_reminder_policy',function(Blueprint $t){$t->unsignedInteger('id')->primary();$t->text('offsets');$t->string('window_start',5)->default('00:00');$t->string('window_end',5)->default('23:59');$t->boolean('enabled')->default(true);$t->timestamps();});}
 public function down(){throw new RuntimeException('Reminder configuration requires explicit recovery.');}
}
