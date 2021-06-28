<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantBorrarTransporte extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('transporte_pasajes');
        Schema::dropIfExists('transporte_user_terminal');
        Schema::dropIfExists('transporte_vehiculo_asientos');
        Schema::dropIfExists('transporte_estado_asientos');
        Schema::dropIfExists('transporte_choferes');
        Schema::dropIfExists('transporte_destinos');
        Schema::dropIfExists('transporte_encomiendas');
        Schema::dropIfExists('transporte_vehiculos');
        Schema::dropIfExists('transporte_rutas');
        Schema::dropIfExists('transporte_terminales');
        Schema::dropIfExists('transporte_estado_encomienda');
        Schema::dropIfExists('transporte_estado_pago_encomienda');
        Schema::dropIfExists('transporte_manifiesto');
        Schema::dropIfExists('transporte_programaciones');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
