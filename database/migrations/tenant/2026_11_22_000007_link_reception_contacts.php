<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class LinkReceptionContacts extends Migration {
 public function up(){
  $s=DB::connection('tenant')->getSchemaBuilder();
  $s->create('aireception_contact_links',function(Blueprint $t){$t->increments('id');$t->unsignedInteger('channel_id');$t->string('sender_hash',64);$t->unsignedInteger('person_id');$t->unsignedInteger('verified_by');$t->string('evidence',255);$t->timestamp('verified_at');$t->unique(['channel_id','sender_hash'],'ar_contact_identity');$t->foreign('person_id')->references('id')->on('persons');});
  $s->table('aireception_conversations',function(Blueprint $t){$t->unsignedInteger('channel_id')->nullable();$t->string('sender_hash',64)->nullable();$t->unique(['channel_id','sender_hash'],'ar_conversation_identity');});
  $s->table('aireception_messages',function(Blueprint $t){$t->unsignedInteger('channel_id')->nullable();$t->string('external_id',255)->nullable();$t->unique(['channel_id','external_id'],'ar_message_external');});
  $s->table('aireception_bookings',function(Blueprint $t){$t->string('origin',30)->default('manual');});
 }
 public function down(){throw new RuntimeException('Identity and message audit require explicit retention review.');}
}
