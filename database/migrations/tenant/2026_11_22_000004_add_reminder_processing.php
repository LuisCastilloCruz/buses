<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class AddReminderProcessing extends Migration {
 public function up(){
  $s=DB::connection('tenant')->getSchemaBuilder();
  $s->table('aireception_reminders',function(Blueprint $t){$t->unsignedInteger('attempts')->default(0);$t->dateTime('processed_at')->nullable();$t->string('last_error',80)->nullable();});
  $s->create('aireception_contact_consents',function(Blueprint $t){$t->increments('id');$t->unsignedInteger('person_id');$t->string('channel',20);$t->boolean('allowed')->default(false);$t->dateTime('expires_at')->nullable();$t->string('evidence',255);$t->unsignedInteger('actor_id');$t->timestamps();$t->unique(['person_id','channel']);$t->foreign('person_id')->references('id')->on('persons');});
  $s->create('aireception_reminder_attempts',function(Blueprint $t){$t->bigIncrements('id');$t->unsignedInteger('reminder_id');$t->string('outcome',40);$t->timestamp('created_at');$t->foreign('reminder_id')->references('id')->on('aireception_reminders');});
 }
 public function down(){throw new RuntimeException('Notification audit requires explicit archival review.');}
}
