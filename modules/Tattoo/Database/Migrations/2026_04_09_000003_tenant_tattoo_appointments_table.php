<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantTattooAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::create('tattoo_appointments', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('person_id');
            $table->unsignedInteger('tattoo_artist_id');
            $table->unsignedInteger('tattoo_style_id')->nullable();

            $table->date('appointment_date');
            $table->time('appointment_time')->nullable();

            $table->string('status')->default('pending');
            $table->string('body_zone')->nullable();
            $table->string('tattoo_size')->nullable();
            $table->decimal('estimated_price', 12, 2)->default(0);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->text('idea')->nullable();
            $table->text('observations')->nullable();

            $table->timestamps();

            $table->foreign('tattoo_artist_id')->references('id')->on('tattoo_artists')->onDelete('cascade');
            $table->foreign('tattoo_style_id')->references('id')->on('tattoo_styles')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tattoo_appointments');
    }
}