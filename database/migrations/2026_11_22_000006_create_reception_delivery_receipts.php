<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionDeliveryReceipts extends Migration {
 public function up(){DB::connection('system')->getSchemaBuilder()->create('aireception_delivery_receipts',function(Blueprint $t){$t->bigIncrements('id');$t->unsignedInteger('channel_id');$t->string('message_id',255);$t->string('status',20);$t->unsignedBigInteger('provider_timestamp');$t->string('event_hash',64)->unique();$t->timestamp('created_at')->nullable();$t->index(['channel_id','message_id'],'ar_receipt_channel_message');});}
 public function down(){throw new RuntimeException('Delivery audit requires explicit recovery.');}
}
