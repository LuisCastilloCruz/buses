<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTattooArtistPortfoliosTable extends Migration
{
    public function up()
    {
        Schema::create('tattoo_artist_portfolios', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tattoo_artist_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('tattoo_artist_id')
                ->references('id')
                ->on('tattoo_artists')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tattoo_artist_portfolios');
    }
}