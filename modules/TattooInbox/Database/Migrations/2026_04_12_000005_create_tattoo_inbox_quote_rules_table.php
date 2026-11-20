<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTattooInboxQuoteRulesTable extends Migration
{
    public function up()
    {
        Schema::create('tattoo_inbox_quote_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rule_key', 100)->unique();
            $table->decimal('rule_value', 12, 4);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tattoo_inbox_quote_rules');
    }
}