<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionAgentTurns extends Migration {
 public function up(){DB::connection('tenant')->getSchemaBuilder()->create('aireception_agent_turns',function(Blueprint $t){$t->bigIncrements('id');$t->unsignedInteger('actor_id');$t->unsignedInteger('person_id');$t->unsignedInteger('conversation_id');$t->string('request_key',80);$t->string('request_hash',64);$t->string('state',20);$t->text('encrypted_result')->nullable();$t->timestamps();$t->unique(['actor_id','request_key'],'ar_agent_turn_key');$t->foreign('conversation_id')->references('id')->on('aireception_conversations');});}
 public function down(){throw new RuntimeException('Conversation turn audit requires explicit recovery.');}
}
