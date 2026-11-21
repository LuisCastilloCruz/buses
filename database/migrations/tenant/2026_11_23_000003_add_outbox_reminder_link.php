<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class AddOutboxReminderLink extends Migration {
 public function up(){DB::connection('tenant')->getSchemaBuilder()->table('aireception_outbox',function(Blueprint $t){$t->unsignedInteger('reminder_id')->nullable();$t->foreign('reminder_id')->references('id')->on('aireception_reminders');});}
 public function down(){throw new RuntimeException('Explicit audit retention review required.');}
}