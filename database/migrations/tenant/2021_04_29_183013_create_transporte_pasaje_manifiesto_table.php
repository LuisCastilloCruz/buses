<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportePasajeManifiestoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transporte_pasaje_manifiesto', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pasaje_id');
            $table->foreign('pasaje_id','tpm_tp_foreing_if')->references('id')->on('transporte_pasajes');
            $table->unsignedInteger('manifiesto_id');
            $table->foreign('manifiesto_id','tpm_tm_foreing_if')->references('id')->on('transporte_manifiesto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transporte_pasaje_manifiesto');
    }
}
