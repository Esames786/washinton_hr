<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // 1. Insert into shift_types
        DB::table('shift_types')->insert([
            [
                'id' => 1,
                'name' => 'Night',
                'shift_start' => '20:00:00',
                'shift_end' => '02:00:00',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Morning',
                'shift_start' => '08:00:00',
                'shift_end' => '14:00:00',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // 3. Insert into shift_attendance_rules (only for Night shift - shift_type_id = 1)
        DB::table('shift_attendance_rules')->insert([
            [
                'shift_type_id' => 1,
                'attendance_status_id' => 1, // Late
                'entry_time' => '18:15:00',
                'entry_weight' => 0.3,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'shift_type_id' => 1,
                'attendance_status_id' => 2, // Present
                'entry_time' => '18:45:00',
                'entry_weight' => 0.5,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'shift_type_id' => 1,
                'attendance_status_id' => 3, // Half Day
                'entry_time' => '21:00:00',
                'entry_weight' => 0.1,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'shift_type_id' => 1,
                'attendance_status_id' => 4, // Early Exit
                'entry_time' => '01:00:00',
                'entry_weight' => 0.3,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'shift_type_id' => 1,
                'attendance_status_id' => 5, // Absent
                'entry_time' => null,
                'entry_weight' => 1.0,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
