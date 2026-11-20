<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class TenantAddDataTattooToModuleLevels extends Migration
{
    public function up()
    {
        $module = DB::table('modules')->where('value', 'tattoo')->first();

        if (!$module) {
            return;
        }

        $levels = [
            [
                'value' => 'tattoo_dashboard',
                'description' => 'Dashboard',
                'module_id' => $module->id,
                'label_menu' => 'TAT',
                'route_name' => 'tenant.tattoo-dashboard.index',
                'route_path' => '/tattoo-dashboard',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'tattoo_appointments',
                'description' => 'Agenda',
                'module_id' => $module->id,
                'label_menu' => 'AGE',
                'route_name' => 'tenant.tattoo-appointments.index',
                'route_path' => '/tattoo-appointments',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'tattoo_artists',
                'description' => 'Artistas',
                'module_id' => $module->id,
                'label_menu' => 'ART',
                'route_name' => 'tenant.tattoo-artists.index',
                'route_path' => '/tattoo-artists',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'tattoo_styles',
                'description' => 'Estilos',
                'module_id' => $module->id,
                'label_menu' => 'EST',
                'route_name' => 'tenant.tattoo-styles.index',
                'route_path' => '/tattoo-styles',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'tattoo_sessions',
                'description' => 'Sesiones',
                'module_id' => $module->id,
                'label_menu' => 'SES',
                'route_name' => 'tenant.tattoo-sessions.index',
                'route_path' => '/tattoo-sessions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'tattoo_consents',
                'description' => 'Consentimientos',
                'module_id' => $module->id,
                'label_menu' => 'CON',
                'route_name' => 'tenant.tattoo-consents.index',
                'route_path' => '/tattoo-consents',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($levels as $level) {
            $exists = DB::table('module_levels')
                ->where('value', $level['value'])
                ->exists();

            if (!$exists) {
                DB::table('module_levels')->insert($level);
            }
        }
    }

    public function down()
    {
        DB::table('module_levels')->whereIn('value', [
            'tattoo_dashboard',
            'tattoo_appointments',
            'tattoo_artists',
            'tattoo_styles',
            'tattoo_sessions',
            'tattoo_consents',
        ])->delete();
    }
}