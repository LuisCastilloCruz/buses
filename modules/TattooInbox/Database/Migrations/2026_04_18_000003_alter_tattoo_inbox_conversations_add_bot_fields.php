<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTattooInboxConversationsAddBotFields extends Migration
{
    public function up()
    {
        Schema::table('tattoo_inbox_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('tattoo_inbox_conversations', 'bot_enabled')) {
                $table->boolean('bot_enabled')->default(true)->after('quote_status');
            }

            if (!Schema::hasColumn('tattoo_inbox_conversations', 'taken_by_user_id')) {
                $table->unsignedInteger('taken_by_user_id')->nullable()->after('bot_enabled');
            }

            if (!Schema::hasColumn('tattoo_inbox_conversations', 'taken_at')) {
                $table->dateTime('taken_at')->nullable()->after('taken_by_user_id');
            }

            if (!Schema::hasColumn('tattoo_inbox_conversations', 'stage')) {
                $table->string('stage')->nullable()->after('taken_at'); 
                // new / waiting_data / quoted / interested / booked / closed
            }
        });
    }

    public function down()
    {
        Schema::table('tattoo_inbox_conversations', function (Blueprint $table) {
            $drop = [];

            if (Schema::hasColumn('tattoo_inbox_conversations', 'bot_enabled')) {
                $drop[] = 'bot_enabled';
            }
            if (Schema::hasColumn('tattoo_inbox_conversations', 'taken_by_user_id')) {
                $drop[] = 'taken_by_user_id';
            }
            if (Schema::hasColumn('tattoo_inbox_conversations', 'taken_at')) {
                $drop[] = 'taken_at';
            }
            if (Schema::hasColumn('tattoo_inbox_conversations', 'stage')) {
                $drop[] = 'stage';
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
}