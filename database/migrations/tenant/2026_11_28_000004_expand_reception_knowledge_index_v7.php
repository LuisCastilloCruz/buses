<?php
use Illuminate\Database\Migrations\Migration;
class ExpandReceptionKnowledgeIndexV7 extends Migration {
 public function up(){DB::connection('tenant')->statement('ALTER TABLE aireception_knowledge_documents MODIFY term_hashes MEDIUMTEXT NOT NULL');}
 public function down(){throw new RuntimeException('Shrinking an index could truncate approved sources.');}
}
