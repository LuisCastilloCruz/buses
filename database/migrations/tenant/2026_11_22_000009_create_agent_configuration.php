<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateAgentConfiguration extends Migration {
 public function up(){
  $s=DB::connection('tenant')->getSchemaBuilder();$s->create('aireception_agent_settings',function(Blueprint $t){$t->unsignedInteger('id')->primary();$t->boolean('enabled')->default(false);$t->string('mode',20)->default('simulated');$t->string('model',100)->nullable();$t->unsignedInteger('max_output_tokens')->default(512);$t->text('encrypted_credentials');$t->timestamps();});
  $s->table('aireception_usage',function(Blueprint $t){$t->string('model',100)->nullable();$t->unsignedInteger('input_tokens')->nullable();$t->unsignedInteger('output_tokens')->nullable();$t->text('encrypted_response')->nullable();});
 }
 public function down(){throw new RuntimeException('Provider credentials and usage require controlled archival.');}
}
