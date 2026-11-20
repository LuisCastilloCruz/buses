<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionPlanControls extends Migration {
 public function up(){
  $s=DB::connection('system')->getSchemaBuilder();
  $s->create('aireception_plan_controls',function(Blueprint $t){$t->unsignedInteger('plan_id')->primary();$t->boolean('allow_reception')->default(false);$t->boolean('allow_dental')->default(false);$t->decimal('addon_price',12,2)->default(0);$t->unsignedBigInteger('monthly_units')->default(0);$t->unsignedInteger('professional_limit')->default(0);$t->unsignedInteger('message_limit')->default(0);$t->timestamps();$t->foreign('plan_id')->references('id')->on('plans');});
  $s->create('aireception_subscriptions',function(Blueprint $t){$t->unsignedInteger('client_id')->primary();$t->string('status',20);$t->dateTime('expires_at')->nullable();$t->timestamps();$t->foreign('client_id')->references('id')->on('clients');});
  $s->create('aireception_system_audit',function(Blueprint $t){$t->bigIncrements('id');$t->unsignedInteger('actor_id');$t->string('action',40);$t->unsignedInteger('subject_id');$t->json('details');$t->timestamp('created_at');});
 }
 public function down(){throw new RuntimeException('Commercial controls require explicit recovery review.');}
}
