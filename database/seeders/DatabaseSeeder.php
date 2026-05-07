<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // 1. All core lookup/status tables (no foreign key dependencies)
            HrSettingSeeder::class,

            // 2. Holidays (standalone)
            HolidaySeeder::class,

            // 3. Shift types + attendance rules
            //    (depends on hr_attendance_statuses seeded in step 1)
            ShiftSettingsSeeder::class,

            // 4. Super admin user + role
            SuperAdminSeeder::class,

            // 5. Pakistan-ready data: departments, designations, employee roles,
            //    shifts (4 shifts), tax slabs (FBR), gratuity, commission,
            //    documents, currency rates, activity fields, sample employees
            PakistanReadySeeder::class,
        ]);
    }
}
