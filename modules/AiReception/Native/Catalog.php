<?php

namespace Modules\AiReception\Native;

class Catalog
{
    public static function definitions() : array
    {
        return ['aireception' => ['AiReception', ['calendar' => 'Calendario', 'book' => 'Gestionar reservas', 'conversations' => 'Conversaciones y derivacion', 'configure' => 'Profesionales, servicios y configuracion']], 'odontologia' => ['Odontologia', ['calendar' => 'Citas y disponibilidad', 'book' => 'Gestionar citas', 'manage' => 'Odontologos, especialidades y sillones', 'patients' => 'Pacientes', 'billing' => 'Servicios y facturacion', 'clinical' => 'Acceso clinico restringido']]];
    }
    public static function install($db) : void
    {
        foreach (self::definitions() as $value => $definition) {
            $module = $db->table('modules')->where('value', $value)->first();
            $values = ['value' => $value, 'description' => $definition[0]];
            if ($db->getSchemaBuilder()->hasColumn('modules', 'sort')) {
                $values['sort'] = max(100, (int) $db->table('modules')->max('sort') + 1);
            }
            if ($db->getSchemaBuilder()->hasColumn('modules', 'order_menu')) {
                $values['order_menu'] = 100;
            }
            if ($module) {
                $db->table('modules')->where('id', $module->id)->update($values);
            }
            $id = $module ? $module->id : $db->table('modules')->insertGetId($values);
            foreach ($definition[1] as $suffix => $description) {
                $key = $value . '_' . $suffix;
                $level = ['module_id' => $id, 'value' => $key, 'description' => $description];
                $page = $suffix === 'conversations' ? 'conversations' : ($suffix === 'configure' ? 'settings' : 'calendar');
                if ($db->getSchemaBuilder()->hasColumn('module_levels', 'route_name')) {
                    $level['route_name'] = 'tenant.' . $value . '.' . $page;
                }
                if ($db->getSchemaBuilder()->hasColumn('module_levels', 'route_path')) {
                    $level['route_path'] = '/' . $value . '/' . $page;
                }
                $db->table('module_levels')->updateOrInsert(['value' => $key], $level);
            }
        }
    }
    public static function syncEntitlements($client, array $moduleValues) : void
    {
        $db = app('db')->connection('system');
        if (!$db->getSchemaBuilder()->hasTable('client_module_entitlements')) {
            return;
        }
        foreach (array_keys(self::definitions()) as $value) {
            $id = $db->table('modules')->where('value', $value)->value('id');
            if (!$id) {
                continue;
            }
            $db->table('client_module_entitlements')->updateOrInsert(['client_id' => $client->id, 'module_id' => $id], ['enabled' => in_array($value, $moduleValues, true), 'updated_at' => date('Y-m-d H:i:s')]);
        }
    }
}
