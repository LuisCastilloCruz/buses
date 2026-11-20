<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionUsageLedger extends Migration
{
    public function up()
    {
        $s=DB::connection('tenant')->getSchemaBuilder();
        $s->create('aireception_budgets',function(Blueprint $t){
            $t->increments('id');$t->unsignedInteger('business_id');$t->string('period',7);
            $t->unsignedBigInteger('limit_units')->default(0);$t->unsignedBigInteger('reserved_units')->default(0);$t->unsignedBigInteger('used_units')->default(0);
            $t->unique(['business_id','period']);
        });
        $s->create('aireception_usage',function(Blueprint $t){
            $t->increments('id');$t->unsignedInteger('budget_id');$t->string('request_key',80);$t->string('request_hash',64);
            $t->string('kind',30);$t->string('state',20);$t->unsignedBigInteger('reserved_units');$t->unsignedBigInteger('used_units')->nullable();$t->timestamps();
            $t->unique(['budget_id','request_key']);$t->foreign('budget_id')->references('id')->on('aireception_budgets');
        });
    }
    public function down(){throw new RuntimeException('Usage ledger rollback requires an explicit archival procedure.');}
}
