<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'Laptop',        'icon' => '💻', 'description' => 'Company issued laptop or notebook computer'],
            ['name' => 'Mobile Phone',  'icon' => '📱', 'description' => 'Company mobile phone or smartphone'],
            ['name' => 'SIM Card',      'icon' => '📡', 'description' => 'Company SIM card for calls and data'],
            ['name' => 'Car',           'icon' => '🚗', 'description' => 'Company vehicle assigned for work use'],
            ['name' => 'Headset',       'icon' => '🎧', 'description' => 'Headset or headphones for calls'],
            ['name' => 'Monitor',       'icon' => '🖥️', 'description' => 'External display monitor'],
            ['name' => 'Keyboard',      'icon' => '⌨️', 'description' => 'External keyboard'],
            ['name' => 'Mouse',         'icon' => '🖱️', 'description' => 'External mouse or pointing device'],
            ['name' => 'USB Dongle',    'icon' => '🔌', 'description' => 'Internet USB dongle / mobile broadband'],
            ['name' => 'Office Chair',  'icon' => '🪑', 'description' => 'Ergonomic or standard office chair'],
            ['name' => 'ID Card',       'icon' => '🪪', 'description' => 'Employee ID / access card'],
            ['name' => 'Tablet',        'icon' => '📋', 'description' => 'Company tablet or iPad'],
        ];

        foreach ($types as $type) {
            DB::table('equipment_types')->insertOrIgnore(array_merge($type, [
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
