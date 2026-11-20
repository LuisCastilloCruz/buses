<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionModelRates extends Migration {
 public function up(){DB::connection('system')->getSchemaBuilder()->create('aireception_model_rates',function(Blueprint $t){$t->string('model',100)->primary();$t->unsignedBigInteger('input_micro_usd_per_million');$t->unsignedBigInteger('output_micro_usd_per_million');$t->dateTime('valid_until');$t->timestamps();});}
 public function down(){throw new RuntimeException('Pricing audit requires explicit review.');}
}
