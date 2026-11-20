<?php

class RegisterExistingInboxCatalogTenant extends \Illuminate\Database\Migrations\Migration
{
    public function up()
    {
        \Modules\TattooInbox\Services\InboxCatalog::install(app('db')->connection('tenant'));
    }
    public function down()
    {
        throw new \RuntimeException('Catalog rollback requires permission review.');
    }
}
