<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class BindBookingDraftConversationV71 extends Migration {
 public function up(){DB::connection('tenant')->getSchemaBuilder()->table('aireception_booking_drafts',function(Blueprint $t){$t->unsignedInteger('conversation_id')->nullable();$t->foreign('conversation_id')->references('id')->on('aireception_conversations');});}
 public function down(){throw new RuntimeException('Explicit retention review required.');}
}
