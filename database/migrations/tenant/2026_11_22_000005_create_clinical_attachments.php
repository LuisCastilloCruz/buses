<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateClinicalAttachments extends Migration {
 public function up(){DB::connection('tenant')->getSchemaBuilder()->create('odontologia_clinical_attachments',function(Blueprint $t){$t->increments('id');$t->unsignedInteger('record_id');$t->string('storage_key',64)->unique();$t->string('mime',40);$t->unsignedInteger('size');$t->string('sha256',64);$t->unsignedInteger('actor_id');$t->timestamp('created_at');$t->foreign('record_id')->references('id')->on('odontologia_clinical_records');});}
 public function down(){throw new RuntimeException('Clinical attachment retention requires explicit review.');}
}
