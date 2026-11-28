<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionDialogueV71 extends Migration {
 public function up(){DB::connection('tenant')->getSchemaBuilder()->create('aireception_dialogue_states',function(Blueprint $t){$t->unsignedInteger('conversation_id')->primary();$t->unsignedInteger('business_id');$t->unsignedInteger('person_id');$t->text('encrypted_state');$t->dateTime('expires_at');$t->dateTime('updated_at');$t->foreign('conversation_id')->references('id')->on('aireception_conversations');});}
 public function down(){throw new RuntimeException('Explicit retention review required.');}
}
