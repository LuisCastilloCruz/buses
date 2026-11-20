<?php

namespace Modules\Odontologia\Database;

use Illuminate\Database\Schema\Blueprint;
class SchemaInstaller
{
    public static function install($db) : void
    {
        $s = $db->getSchemaBuilder();
        if ($s->hasTable('odontologia_dentists')) {
            return;
        }
        $s->create('odontologia_specialties', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->unique();
            $t->boolean('active')->default(true);
        });
        $s->create('odontologia_dentists', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('professional_id')->unique();
            $t->unsignedInteger('specialty_id');
            $t->string('registration')->nullable();
            $t->boolean('active')->default(true);
            $t->foreign('professional_id')->references('id')->on('aireception_professionals');
            $t->foreign('specialty_id')->references('id')->on('odontologia_specialties');
        });
        $s->create('odontologia_chairs', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('resource_id')->unique();
            $t->string('room');
            $t->foreign('resource_id')->references('id')->on('aireception_resources');
        });
    }
}
