<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldSucursalPasajes extends Migration
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

            $table->unsignedInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('id')->on('transporte_terminales');

            $table->string('color')->nullable();

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
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
