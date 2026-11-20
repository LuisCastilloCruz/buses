<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTattooInboxSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('tattoo_inbox_settings', function (Blueprint $table) {
            $table->increments('id');

            $table->string('studio_name')->nullable();
            $table->string('artist_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('district')->nullable();
            $table->string('address')->nullable();

            $table->boolean('bot_enabled')->default(true);
            $table->boolean('auto_quote_enabled')->default(true);
            $table->boolean('human_takeover_enabled')->default(true);
            $table->boolean('openai_enabled')->default(false);
            $table->boolean('openai_vision_enabled')->default(true);
            $table->text('openai_api_key')->nullable();
            $table->string('openai_model', 80)->nullable();
            $table->string('openai_vision_detail', 20)->default('auto');

            $table->text('assistant_prompt')->nullable();
            $table->text('assistant_tone')->nullable();
            $table->text('booking_instructions')->nullable();
            $table->text('aftercare_instructions')->nullable();

            $table->string('meta_verify_token')->nullable();
            $table->string('meta_graph_version')->nullable();
            $table->text('meta_page_access_token')->nullable();
            $table->text('whatsapp_access_token')->nullable();
            $table->string('whatsapp_phone_number_id')->nullable();
            $table->string('instagram_page_id')->nullable();

            $table->string('currency', 10)->default('PEN');

            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tattoo_inbox_settings');
    }
}
