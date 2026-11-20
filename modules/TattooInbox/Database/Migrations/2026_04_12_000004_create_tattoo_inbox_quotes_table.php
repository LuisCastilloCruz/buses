<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTattooInboxQuotesTable extends Migration
{
    public function up()
    {
        Schema::create('tattoo_inbox_quotes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('conversation_id');
            $table->unsignedInteger('lead_id');
            $table->unsignedInteger('tattoo_artist_id')->nullable();
            $table->string('body_zone')->nullable();
            $table->string('style')->nullable();
            $table->decimal('size_cm', 10, 2)->nullable();
            $table->string('size_text')->nullable();
            $table->boolean('is_color')->default(false);
            $table->boolean('is_coverup')->default(false);
            $table->string('detail_level', 30)->nullable();
            $table->boolean('has_reference')->default(false);
            $table->integer('reference_count')->default(0);
            $table->longText('customer_idea')->nullable();
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();
            $table->decimal('suggested_price', 10, 2)->nullable();
            $table->string('currency', 10)->default('PEN');
            $table->string('status', 30)->default('draft');
            $table->timestamp('quoted_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')
                ->references('id')
                ->on('tattoo_inbox_conversations')
                ->onDelete('cascade');

            $table->foreign('lead_id')
                ->references('id')
                ->on('tattoo_inbox_leads')
                ->onDelete('cascade');

            $table->index(['conversation_id', 'status']);
            $table->index(['lead_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tattoo_inbox_quotes');
    }
}