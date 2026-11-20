<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionInboundMessages extends Migration {
 public function up(){DB::connection('tenant')->getSchemaBuilder()->create('aireception_inbound_messages',function(Blueprint $t){$t->bigIncrements('id');$t->unsignedInteger('channel_id');$t->string('message_id',255);$t->string('sender_identity',160);$t->string('type',20);$t->text('encrypted_content');$t->string('state',30)->default('awaiting_identity');$t->unsignedBigInteger('provider_timestamp')->nullable();$t->timestamp('created_at');$t->unique(['channel_id','message_id'],'ar_inbound_dedup');});}
 public function down(){throw new RuntimeException('Inbound message retention requires explicit review.');}
}
