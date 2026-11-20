<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantTattooConsentsTable extends Migration
{
    public function up()
    {
        Schema::create('tattoo_consents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tattoo_appointment_id');
            $table->date('signed_date')->nullable();
            $table->boolean('accepted')->default(false);
            $table->text('medical_notes')->nullable();
            $table->text('allergies')->nullable();
            $table->text('observations')->nullable();
            $table->string('signature_name')->nullable();
            $table->timestamps();

            $table->foreign('tattoo_appointment_id')
                ->references('id')
                ->on('tattoo_appointments')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tattoo_consents');
    }
}