<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeriesTransporte extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::table('cat_document_types')->insert([
            'id' => 33,
            'active' => 1,
            'short' => '',
            'description' => 'MANIFIESTO DE PASAJEROS'
        ]);

        DB::table('cat_document_types')->insert([
            'id' => 100,
            'active' => 1,
            'short' => '',
            'description' => 'MANIFIESTO DE ENCOMIENDAS'
        ]);


        DB::table('series')->insert([
            'establishment_id' => 1,
            'document_type_id' => 33,
            'number' => '0001',
            'contingency' => 0
        ]);

        DB::table('series')->insert([
            'establishment_id' => 1,
            'document_type_id' => 100,
            'number' => '0001',
            'contingency' => 0
        ]);

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
