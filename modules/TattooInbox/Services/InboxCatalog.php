<?php

namespace Modules\TattooInbox\Services;

use Modules\AiReception\Native\Access;
/** Restores the catalog values already consumed by the existing TattooInbox sidebar. */
class InboxCatalog
{
    public static function install($db) : void
    {
        $id = $db->table('modules')->where('value', 'tattooinbox')->value('id');
        if (!$id) {
            $data = ['value' => 'tattooinbox', 'description' => 'Tattoo Inbox'];
            foreach (['sort', 'order_menu'] as $column) {
                if ($db->getSchemaBuilder()->hasColumn('modules', $column)) {
                    $data[$column] = 99;
                }
            }
            $id = $db->table('modules')->insertGetId($data);
        }
        foreach (['tattoo_inbox' => ['Conversaciones', 'tenant.tattoo-inbox.index', '/tattoo-inbox'], 'tattoo_inbox_settings' => ['Configuracion Inbox', 'tenant.tattoo-inbox.settings', '/tattoo-inbox-settings']] as $key => $row) {
            if ($db->table('module_levels')->where('value', $key)->exists()) {
                continue;
            }
            $data = ['module_id' => $id, 'value' => $key, 'description' => $row[0]];
            foreach (['route_name' => 1, 'route_path' => 2] as $col => $i) {
                if ($db->getSchemaBuilder()->hasColumn('module_levels', $col)) {
                    $data[$col] = $row[$i];
                }
            }
            $db->table('module_levels')->insert($data);
        }
    }
}
