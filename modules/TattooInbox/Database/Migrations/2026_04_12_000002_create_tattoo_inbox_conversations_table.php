<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTattooInboxConversationsTable extends Migration
{
    public function up()
    {
        Schema::create('tattoo_inbox_conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('lead_id');
            $table->string('channel', 30);
            $table->string('external_thread_id')->nullable();
            $table->string('status', 30)->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('last_incoming_at')->nullable();
            $table->timestamp('last_outgoing_at')->nullable();
            $table->string('quote_status', 30)->default('pending_data');
            $table->unsignedInteger('assigned_user_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('lead_id')
                ->references('id')
                ->on('tattoo_inbox_leads')
                ->onDelete('cascade');

            $table->index(['channel', 'status']);
            $table->index(['quote_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tattoo_inbox_conversations');
    }
}