<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionChannelRegistry extends Migration {
 public function up(){
  $s=DB::connection('system')->getSchemaBuilder();
  $s->create('aireception_channel_ownership',function(Blueprint $t){$t->string('phone_id',64)->primary();$t->unsignedInteger('website_id');$t->string('owner',30);});
  $s->create('aireception_channels',function(Blueprint $t){$t->increments('id');$t->unsignedInteger('website_id');$t->string('phone_id',64)->unique();$t->string('label',100);$t->boolean('enabled')->default(false);$t->text('encrypted_settings');$t->timestamps();});
  $s->create('aireception_webhook_events',function(Blueprint $t){$t->bigIncrements('id');$t->unsignedInteger('channel_id');$t->string('message_id',255);$t->text('encrypted_payload');$t->string('state',30)->default('pending');$t->timestamps();$t->unique(['channel_id','message_id'],'ar_event_dedup');$t->foreign('channel_id')->references('id')->on('aireception_channels');});
 }
 public function down(){throw new RuntimeException('Channel registry and webhook audit require explicit recovery.');}
}
