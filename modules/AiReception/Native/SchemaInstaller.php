<?php

namespace Modules\AiReception\Native;

use Illuminate\Database\Schema\Blueprint;
class SchemaInstaller
{
    public static function install($db) : void
    {
        $s = $db->getSchemaBuilder();
        $tables = ['professionals', 'services', 'professional_services', 'bookings', 'commands', 'events', 'resources', 'professional_resources', 'professional_links', 'conversations', 'messages', 'reminders'];
        $present = 0;
        foreach ($tables as $table) {
            if ($s->hasTable('aireception_' . $table)) {
                $present++;
            }
        }
        if ($present === count($tables) && $s->hasColumn('aireception_bookings', 'person_id') && $s->hasColumn('aireception_services', 'item_id')) {
            return;
        }
        if ($present) {
            throw new \RuntimeException('Partial or incompatible agenda schema: inspect and recover explicitly before migrating.');
        }
        $s->create('aireception_professionals', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('business_id');
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->json('policy');
            $t->unique(['business_id', 'id']);
        });
        $s->create('aireception_services', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('business_id');
            $t->unsignedInteger('item_id')->nullable();
            $t->string('name');
            $t->unsignedInteger('duration_minutes');
            $t->unsignedInteger('buffer_minutes')->default(0);
            $t->boolean('active')->default(true);
            $t->unique(['business_id', 'id']);
            $t->foreign('item_id')->references('id')->on('items');
        });
        $s->create('aireception_professional_services', function (Blueprint $t) {
            $t->unsignedInteger('business_id');
            $t->unsignedInteger('professional_id');
            $t->unsignedInteger('service_id');
            $t->primary(['business_id', 'professional_id', 'service_id'], 'ar_native_ps_pk');
            $t->foreign(['business_id', 'professional_id'], 'ar_native_ps_prof')->references(['business_id', 'id'])->on('aireception_professionals');
            $t->foreign(['business_id', 'service_id'], 'ar_native_ps_service')->references(['business_id', 'id'])->on('aireception_services');
        });
        $s->create('aireception_bookings', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('business_id');
            $t->unsignedInteger('professional_id');
            $t->unsignedInteger('service_id');
            $t->unsignedInteger('person_id')->nullable();
            $t->string('client_name', 100);
            $t->date('day');
            $t->string('start', 5);
            $t->string('end', 5);
            $t->unsignedInteger('duration_minutes');
            $t->unsignedInteger('buffer_minutes');
            $t->string('status');
            $t->unsignedInteger('version')->default(1);
            $t->timestamps();
            $t->unique(['business_id', 'id']);
            $t->index(['business_id', 'professional_id', 'day'], 'ar_native_calendar');
            $t->foreign('person_id')->references('id')->on('persons');
            $t->foreign(['business_id', 'professional_id'], 'ar_native_book_prof')->references(['business_id', 'id'])->on('aireception_professionals');
            $t->foreign(['business_id', 'service_id'], 'ar_native_book_service')->references(['business_id', 'id'])->on('aireception_services');
        });
        $s->create('aireception_commands', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('business_id');
            $t->unsignedInteger('actor_id');
            $t->string('request_key', 80);
            $t->string('request_hash', 64);
            $t->json('result');
            $t->timestamp('created_at');
            $t->unique(['business_id', 'actor_id', 'request_key'], 'ar_native_idempotency');
            $t->foreign('actor_id')->references('id')->on('users');
        });
        $s->create('aireception_events', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('business_id');
            $t->unsignedInteger('actor_id');
            $t->unsignedInteger('booking_id')->nullable();
            $t->string('action');
            $t->json('details');
            $t->timestamp('created_at');
            $t->foreign('actor_id')->references('id')->on('users');
            $t->foreign(['business_id', 'booking_id'], 'ar_native_event_booking')->references(['business_id', 'id'])->on('aireception_bookings');
        });
        $s->create('aireception_resources', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('business_id');
            $t->string('name');
            $t->boolean('active')->default(true);
        });
        $s->create('aireception_professional_resources', function (Blueprint $t) {
            $t->unsignedInteger('professional_id');
            $t->unsignedInteger('resource_id');
            $t->primary(['professional_id', 'resource_id'], 'ar_native_pr_pk');
            $t->foreign('professional_id')->references('id')->on('aireception_professionals');
            $t->foreign('resource_id')->references('id')->on('aireception_resources');
        });
        $s->create('aireception_professional_links', function (Blueprint $t) {
            $t->unsignedInteger('professional_id')->primary();
            $t->string('domain', 30);
            $t->unsignedInteger('source_id');
            $t->unique(['domain', 'source_id']);
            $t->foreign('professional_id')->references('id')->on('aireception_professionals');
        });
        $s->create('aireception_conversations', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('person_id');
            $t->string('status')->default('automated');
            $t->unsignedInteger('assigned_user_id')->nullable();
            $t->timestamps();
            $t->foreign('person_id')->references('id')->on('persons');
            $t->foreign('assigned_user_id')->references('id')->on('users');
        });
        $s->create('aireception_messages', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('conversation_id');
            $t->string('direction');
            $t->text('body');
            $t->timestamp('created_at');
            $t->foreign('conversation_id')->references('id')->on('aireception_conversations');
        });
        $s->create('aireception_reminders', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('booking_id');
            $t->dateTime('scheduled_at');
            $t->string('status')->default('simulated_pending');
            $t->unique(['booking_id', 'scheduled_at']);
            $t->foreign('booking_id')->references('id')->on('aireception_bookings');
        });
    }
}
