<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantTattooSessionsTable extends Migration
{
    public function up()
    {
        Schema::create('tattoo_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tattoo_appointment_id');
            $table->unsignedInteger('session_number')->default(1);
            $table->date('session_date')->nullable();
            $table->time('session_time')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('payment_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tattoo_appointment_id')
                ->references('id')
                ->on('tattoo_appointments')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tattoo_sessions');
    }
}