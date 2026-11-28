<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionKnowledgeV7 extends Migration {
 public function up(){DB::connection('tenant')->getSchemaBuilder()->create('aireception_knowledge_documents',function(Blueprint $t){$t->increments('id');$t->unsignedInteger('business_id');$t->string('source',80);$t->unsignedInteger('version');$t->string('title',160);$t->string('content_hash',64);$t->text('encrypted_body')->nullable();$t->text('term_hashes');$t->boolean('active')->default(true);$t->unsignedInteger('actor_id');$t->timestamps();$t->unique(['business_id','source','version'],'ar_knowledge_version');$t->index(['business_id','active'],'ar_knowledge_active');});}
 public function down(){throw new RuntimeException('Knowledge rollback requires explicit retention review.');}
}
