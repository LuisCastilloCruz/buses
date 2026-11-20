<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTattooInboxMetaChannelsTable extends Migration
{
    protected function getSystemConnectionName(): string
    {
        return config('tenancy.db.system-connection-name')
            ?: config('tenancy.system_connection_name')
            ?: 'system';
    }

    public function up()
    {
        $connection = $this->getSystemConnectionName();

        Schema::connection($connection)->create('tattoo_inbox_meta_channels', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('tenant_website_id');
            $table->unsignedBigInteger('tenant_hostname_id')->nullable();
            $table->string('tenant_fqdn', 255)->nullable();

            $table->string('platform', 30); // whatsapp | instagram
            $table->string('meta_identifier_type', 60); // whatsapp_phone_number_id | instagram_page_id
            $table->string('meta_identifier_value', 191);

            $table->string('label', 191)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_payload_at')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(
                ['platform', 'meta_identifier_type', 'meta_identifier_value'],
                'tattoo_inbox_meta_channels_unique_identifier'
            );

            $table->index(['tenant_website_id'], 'tattoo_inbox_meta_channels_website_idx');
            $table->index(['tenant_hostname_id'], 'tattoo_inbox_meta_channels_hostname_idx');
            $table->index(['tenant_fqdn'], 'tattoo_inbox_meta_channels_fqdn_idx');
            $table->index(['platform'], 'tattoo_inbox_meta_channels_platform_idx');
            $table->index(['is_active'], 'tattoo_inbox_meta_channels_active_idx');
        });
    }

    public function down()
    {
        $connection = $this->getSystemConnectionName();

        Schema::connection($connection)->dropIfExists('tattoo_inbox_meta_channels');
    }
}