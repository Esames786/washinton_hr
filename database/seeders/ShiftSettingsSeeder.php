<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSettingsSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        DB::table('hr_shift_types')->insert([
            ['id' => 1, 'name' => 'Night',   'shift_start' => '20:00:00', 'shift_end' => '02:00:00', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Morning', 'shift_start' => '08:00:00', 'shift_end' => '14:00:00', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('hr_shift_attendance_rules')->insert([
            ['shift_type_id' => 1, 'attendance_status_id' => 1, 'entry_time' => '18:15:00', 'entry_weight' => 0.3, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['shift_type_id' => 1, 'attendance_status_id' => 2, 'entry_time' => '18:45:00', 'entry_weight' => 0.5, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['shift_type_id' => 1, 'attendance_status_id' => 3, 'entry_time' => '21:00:00', 'entry_weight' => 0.1, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['shift_type_id' => 1, 'attendance_status_id' => 4, 'entry_time' => '01:00:00', 'entry_weight' => 0.3, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['shift_type_id' => 1, 'attendance_status_id' => 5, 'entry_time' => null,        'entry_weight' => 1.0, 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
