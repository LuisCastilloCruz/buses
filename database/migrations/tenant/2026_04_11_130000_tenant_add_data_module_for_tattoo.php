<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class TenantAddDataModuleForTattoo extends Migration
{
    public function up()
    {
        $exists = DB::table('modules')->where('value', 'tattoo')->exists();

        if (!$exists) {
            DB::table('modules')->insert([
                [
                    'value' => 'tattoo',
                    'description' => 'Tattoo',
                    'order_menu' => 20,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
    }

    public function down()
    {
        DB::table('modules')->where('value', 'tattoo')->delete();
    }
}