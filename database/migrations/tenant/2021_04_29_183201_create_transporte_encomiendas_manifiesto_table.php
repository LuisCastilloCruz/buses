<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransporteEncomiendasManifiestoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transporte_encomiendas_manifiesto', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('encomienda_id');
            $table->foreign('encomienda_id','tem_te_foreign_id')->references('id')->on('transporte_encomiendas');
            $table->unsignedInteger('manifiesto_id');
            $table->foreign('manifiesto_id','tem_tm_foreign_id')->references('id')->on('transporte_manifiesto');
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
        Schema::dropIfExists('transporte_encomiendas_manifiesto');
    }
}
