<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class ProtectReceivedConversationContent extends Migration {
 public function up(){DB::connection('tenant')->getSchemaBuilder()->table('aireception_messages',function(Blueprint $t){$t->boolean('is_encrypted')->default(false);$t->unsignedBigInteger('provider_timestamp')->nullable();});}
 public function down(){throw new RuntimeException('Encrypted content cannot be downgraded automatically.');}
}
