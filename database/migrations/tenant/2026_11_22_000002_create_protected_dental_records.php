<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateProtectedDentalRecords extends Migration
{
    public function up(){
        $db=DB::connection('tenant');$s=$db->getSchemaBuilder();
        $s->create('odontologia_clinical_records',function(Blueprint $t){
            $t->increments('id');$t->unsignedInteger('person_id');$t->string('kind',30);$t->text('encrypted_content');$t->unsignedInteger('version')->default(1);$t->unsignedInteger('author_id');$t->timestamps();
            $t->foreign('person_id')->references('id')->on('persons');$t->foreign('author_id')->references('id')->on('users');
        });
        $s->create('odontologia_clinical_audit',function(Blueprint $t){
            $t->bigIncrements('id');$t->unsignedInteger('record_id')->nullable();$t->unsignedInteger('person_id');$t->unsignedInteger('actor_id');$t->string('action',30);$t->unsignedInteger('version')->nullable();$t->text('encrypted_snapshot')->nullable();$t->timestamp('created_at');
            $t->index(['person_id','created_at']);
        });
        Modules\AiReception\Native\Catalog::install($db);
    }
    public function down(){throw new RuntimeException('Clinical records require a reviewed retention and recovery procedure.');}
}
