<?php

use App\Models\System\Module;
use App\Models\System\ModuleLevel;
use Illuminate\Database\Migrations\Migration;

class AddLevelsTattooToModuleLevelsTable extends Migration
{
    public function up()
    {
        $module = Module::where('value', 'tattoo')->first();

        if (!$module) {
            return;
        }

        $levels = [
            [
                'module_id' => $module->id,
                'value' => 'tattoo_dashboard',
                'description' => 'Dashboard',
                'route_name' => 'tenant.tattoo-dashboard.index',
                'label_menu' => 'Dashboard',
                'route_path' => 'tattoo-dashboard',
            ],
            [
                'module_id' => $module->id,
                'value' => 'tattoo_appointments',
                'description' => 'Agenda',
                'route_name' => 'tenant.tattoo-appointments.index',
                'label_menu' => 'Agenda',
                'route_path' => 'tattoo-appointments',
            ],
            [
                'module_id' => $module->id,
                'value' => 'tattoo_artists',
                'description' => 'Artistas',
                'route_name' => 'tenant.tattoo-artists.index',
                'label_menu' => 'Artistas',
                'route_path' => 'tattoo-artists',
            ],
            [
                'module_id' => $module->id,
                'value' => 'tattoo_styles',
                'description' => 'Estilos',
                'route_name' => 'tenant.tattoo-styles.index',
                'label_menu' => 'Estilos',
                'route_path' => 'tattoo-styles',
            ],
            [
                'module_id' => $module->id,
                'value' => 'tattoo_sessions',
                'description' => 'Sesiones',
                'route_name' => 'tenant.tattoo-sessions.index',
                'label_menu' => 'Sesiones',
                'route_path' => 'tattoo-sessions',
            ],
            [
                'module_id' => $module->id,
                'value' => 'tattoo_consents',
                'description' => 'Consentimientos',
                'route_name' => 'tenant.tattoo-consents.index',
                'label_menu' => 'Consentimientos',
                'route_path' => 'tattoo-consents',
            ],
        ];

        foreach ($levels as $level) {
            $exists = ModuleLevel::where('module_id', $level['module_id'])
                ->where('value', $level['value'])
                ->exists();

            if (!$exists) {
                ModuleLevel::create($level);
            }
        }
    }

    public function down()
    {
        $module = Module::where('value', 'tattoo')->first();

        if (!$module) {
            return;
        }

        ModuleLevel::where('module_id', $module->id)
            ->whereIn('value', [
                'tattoo_dashboard',
                'tattoo_appointments',
                'tattoo_artists',
                'tattoo_styles',
                'tattoo_sessions',
                'tattoo_consents',
            ])
            ->delete();
    }
}