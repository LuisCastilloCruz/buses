<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTattooInboxMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('tattoo_inbox_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('conversation_id');
            $table->unsignedInteger('lead_id');
            $table->string('channel', 30);
            $table->string('direction', 10); // in | out
            $table->string('message_type', 30)->default('text');
            $table->string('external_message_id')->nullable();
            $table->longText('message_text')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_mime')->nullable();
            $table->json('payload')->nullable();
            $table->unsignedInteger('sent_by_user_id')->nullable();
            $table->boolean('is_automated')->default(false);
            $table->string('status', 30)->default('received');
            $table->timestamp('received_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')
                ->references('id')
                ->on('tattoo_inbox_conversations')
                ->onDelete('cascade');

            $table->foreign('lead_id')
                ->references('id')
                ->on('tattoo_inbox_leads')
                ->onDelete('cascade');

            $table->index(['conversation_id', 'direction']);
            $table->index(['channel', 'external_message_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tattoo_inbox_messages');
    }
}