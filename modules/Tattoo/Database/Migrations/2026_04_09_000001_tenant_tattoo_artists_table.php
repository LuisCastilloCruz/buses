<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantTattooArtistsTable extends Migration
{
    public function up()
    {
        Schema::create('tattoo_artists', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('stage_name')->nullable();
            $table->string('specialty')->nullable();
            $table->text('bio')->nullable();
            $table->string('instagram')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tattoo_artists');
    }
}