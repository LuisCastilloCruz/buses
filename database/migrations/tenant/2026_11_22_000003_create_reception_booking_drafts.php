<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
class CreateReceptionBookingDrafts extends Migration {
    public function up(){DB::connection('tenant')->getSchemaBuilder()->create('aireception_booking_drafts',function(Blueprint $t){
        $t->increments('id');$t->unsignedInteger('business_id');$t->unsignedInteger('actor_id');$t->string('token_hash',64)->unique();$t->json('payload');$t->dateTime('expires_at');$t->json('result')->nullable();$t->timestamp('created_at');$t->foreign('actor_id')->references('id')->on('users');
    });}
    public function down(){throw new RuntimeException('Booking proposals require explicit retention review.');}
}
