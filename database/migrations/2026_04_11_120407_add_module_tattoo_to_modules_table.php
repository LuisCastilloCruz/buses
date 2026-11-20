<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddModuleTattooToModulesTable extends Migration
{
    public function up()
    {
        $exists = DB::table('modules')->where('value', 'tattoo')->exists();

        if (!$exists) {
            DB::table('modules')->insert([
                'value' => 'tattoo',
                'description' => 'Tattoo',
                'sort' => 21,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('modules')->where('value', 'tattoo')->delete();
    }
}