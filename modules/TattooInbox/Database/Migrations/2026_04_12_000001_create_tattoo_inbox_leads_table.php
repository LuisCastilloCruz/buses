<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTattooInboxLeadsTable extends Migration
{
    public function up()
    {
        Schema::create('tattoo_inbox_leads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('channel', 30);
            $table->string('external_user_id', 191);
            $table->string('full_name')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('instagram_username')->nullable();
            $table->string('email')->nullable();
            $table->string('status', 30)->default('new');
            $table->timestamp('last_message_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['channel', 'external_user_id']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tattoo_inbox_leads');
    }
}