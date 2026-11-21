<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionV5Transport extends Migration {
 public function up(){
  $s=DB::connection('tenant')->getSchemaBuilder();
  $s->create('aireception_outbox',function(Blueprint $t){
   $t->bigIncrements('id');$t->unsignedInteger('business_id');$t->unsignedInteger('channel_id');$t->unsignedInteger('conversation_id');$t->unsignedInteger('person_id');
   $t->string('request_key',80);$t->string('request_hash',64);$t->text('encrypted_payload');$t->string('state',32)->default('pending');$t->string('mode',16);$t->unsignedInteger('attempts')->default(0);$t->dateTime('available_at');$t->dateTime('claimed_at')->nullable();$t->string('provider_id')->nullable();$t->string('last_error',64)->nullable();$t->unsignedInteger('booking_id')->nullable();$t->unsignedInteger('booking_version')->nullable();$t->timestamps();
   $t->unique(['business_id','request_key'],'ar_outbox_dedup');$t->unique(['channel_id','provider_id'],'ar_outbox_provider');$t->index(['state','available_at']);$t->foreign('conversation_id')->references('id')->on('aireception_conversations');
  });
  $s->create('aireception_outbox_attempts',function(Blueprint $t){$t->bigIncrements('id');$t->unsignedBigInteger('outbox_id');$t->string('outcome',32);$t->dateTime('created_at');$t->foreign('outbox_id')->references('id')->on('aireception_outbox');});
  $s->create('aireception_channel_turns',function(Blueprint $t){$t->bigIncrements('id');$t->unsignedInteger('channel_id');$t->unsignedInteger('conversation_id');$t->string('message_id');$t->string('state',32);$t->text('encrypted_result')->nullable();$t->timestamps();$t->unique(['channel_id','message_id'],'ar_channel_turn_dedup');});
  $s->create('aireception_pending_confirmations',function(Blueprint $t){$t->increments('id');$t->unsignedInteger('conversation_id')->unique();$t->text('encrypted_token');$t->string('code',12);$t->dateTime('expires_at');$t->unsignedInteger('actor_id');});
 }
 public function down(){throw new RuntimeException('Explicit archival review required.');}
}