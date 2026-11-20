<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOpenaiFieldsToTattooInboxSettings extends Migration
{
    public function up()
    {
        Schema::table('tattoo_inbox_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('tattoo_inbox_settings', 'openai_enabled')) {
                $table->boolean('openai_enabled')->default(false)->after('human_takeover_enabled');
            }

            if (!Schema::hasColumn('tattoo_inbox_settings', 'openai_vision_enabled')) {
                $table->boolean('openai_vision_enabled')->default(true)->after('openai_enabled');
            }

            if (!Schema::hasColumn('tattoo_inbox_settings', 'openai_api_key')) {
                $table->text('openai_api_key')->nullable()->after('openai_vision_enabled');
            }

            if (!Schema::hasColumn('tattoo_inbox_settings', 'openai_model')) {
                $table->string('openai_model', 80)->nullable()->after('openai_api_key');
            }

            if (!Schema::hasColumn('tattoo_inbox_settings', 'openai_vision_detail')) {
                $table->string('openai_vision_detail', 20)->default('auto')->after('openai_model');
            }
        });
    }

    public function down()
    {
        Schema::table('tattoo_inbox_settings', function (Blueprint $table) {
            $drop = [];

            foreach ([
                'openai_enabled',
                'openai_vision_enabled',
                'openai_api_key',
                'openai_model',
                'openai_vision_detail',
            ] as $column) {
                if (Schema::hasColumn('tattoo_inbox_settings', $column)) {
                    $drop[] = $column;
                }
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
}
