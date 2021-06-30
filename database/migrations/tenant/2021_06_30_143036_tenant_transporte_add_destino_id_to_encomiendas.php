<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantTransporteAddDestinoIdToEncomiendas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transporte_encomiendas', function (Blueprint $table) {
            $table->boolean('origen_id')->after('programacion_id');
            $table->boolean('destino_id')->after('origen_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transporte_encomiendas', function (Blueprint $table) {
            $table->dropColumn('origen_id');
            $table->dropColumn('destino_id');
        });
    }
}
