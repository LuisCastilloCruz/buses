<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionQueue extends Migration {
 public function up(){
  $s=DB::connection('system')->getSchemaBuilder();
  $s->create('aireception_jobs',function(Blueprint $t){$t->bigIncrements('id');$t->string('queue')->index();$t->longText('payload');$t->unsignedTinyInteger('attempts');$t->unsignedInteger('reserved_at')->nullable();$t->unsignedInteger('available_at');$t->unsignedInteger('created_at');});
  $s->create('aireception_failed_jobs',function(Blueprint $t){$t->bigIncrements('id');$t->text('connection');$t->text('queue');$t->longText('payload');$t->longText('exception');$t->timestamp('failed_at')->useCurrent();});
 }
 public function down(){throw new RuntimeException('Queue rollback requires draining and audit.');}
}
