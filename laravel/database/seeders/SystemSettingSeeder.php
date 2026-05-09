<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Edutrack LMS', 'type' => 'string', 'group' => 'general', 'description' => 'Application name'],
            ['key' => 'site_logo', 'value' => '/assets/images/logo.png', 'type' => 'string', 'group' => 'general', 'description' => 'Site logo path'],
            ['key' => 'currency', 'value' => 'ZMW', 'type' => 'string', 'group' => 'general', 'description' => 'Default currency'],
            ['key' => 'registration_fee', 'value' => '150.00', 'type' => 'float', 'group' => 'payments', 'description' => 'Student registration fee'],
            ['key' => 'certificate_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable certificate generation'],
            ['key' => 'google_login_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable Google OAuth'],
            ['key' => 'lenco_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable Lenco payments'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'system', 'description' => 'Maintenance mode status'],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(['key' => $setting['key']], $setting);
        }
    }
}
