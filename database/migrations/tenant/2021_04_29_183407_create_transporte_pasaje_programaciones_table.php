<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportePasajeProgramacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transporte_pasaje_programaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('programacion_id');
            $table->foreign('programacion_id','tppr_tp_foreign_id')->references('id')->on('transporte_programaciones');
            $table->unsignedInteger('pasaje_id');
            $table->foreign('pasaje_id','tppr_tpp_foreign_id')->references('id')->on('transporte_pasajes');
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
        Schema::dropIfExists('transporte_pasaje_programaciones_rutas');
    }
}
