<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTattooInboxPromptRulesTable extends Migration
{
    public function up()
    {
        Schema::create('tattoo_inbox_prompt_rules', function (Blueprint $table) {
            $table->increments('id');

            $table->string('rule_type'); // prompt_rule, pricing_rule, blocked_topic, quick_reply
            $table->string('rule_key');
            $table->text('rule_value')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tattoo_inbox_prompt_rules');
    }
}