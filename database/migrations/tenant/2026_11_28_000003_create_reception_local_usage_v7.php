<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionLocalUsageV7 extends Migration {
 public function up(){DB::connection('tenant')->getSchemaBuilder()->create('aireception_local_usage',function(Blueprint $t){$t->increments('id');$t->unsignedInteger('business_id');$t->string('period',7);$t->string('request_key',80);$t->string('request_hash',64);$t->string('model',100);$t->string('state',20);$t->unsignedInteger('input_tokens')->default(0);$t->unsignedInteger('output_tokens')->default(0);$t->text('encrypted_response')->nullable();$t->timestamps();$t->unique(['business_id','period','request_key'],'ar_local_usage_request');});}
 public function down(){throw new RuntimeException('Usage rollback requires explicit archival review.');}
}
