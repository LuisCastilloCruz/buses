<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhotoPathToTattooArtistsTable extends Migration
{
    public function up()
    {
        Schema::table('tattoo_artists', function (Blueprint $table) {
            if (!Schema::hasColumn('tattoo_artists', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('instagram');
            }
        });
    }

    public function down()
    {
        Schema::table('tattoo_artists', function (Blueprint $table) {
            if (Schema::hasColumn('tattoo_artists', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
        });
    }
}