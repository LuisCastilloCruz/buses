<?php
namespace Modules\TattooInbox\Services;
/** Laravel 5.7 resolves the class from the historical filename, not its declaration. */
class CatalogMigrationCompatibility extends \Illuminate\Database\Migrations\Migration
{
    public function up(){
        $connection=app('db')->getDefaultConnection();
        if(!in_array($connection,['system','tenant'],true))throw new \RuntimeException('Unknown catalog migration context.');
        InboxCatalog::install(app('db')->connection($connection));
    }
    public function down(){throw new \RuntimeException('Catalog rollback requires permission review.');}
}
