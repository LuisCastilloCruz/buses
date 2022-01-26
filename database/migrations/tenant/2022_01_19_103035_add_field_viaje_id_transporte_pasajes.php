<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldViajeIdTransportePasajes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transporte_pasajes', function (Blueprint $table) {
            //
            $table->unsignedInteger('viaje_id');
            $table->foreign('viaje_id')->references('id')->on('transporte_viajes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transporte_pasajes', function (Blueprint $table) {
            //
        });
    }
}
