<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // 1. Core lookup tables — no dependencies
            HrSettingSeeder::class,          // employment_types, employee_statuses, commission_types,
                                             // payroll_statuses, leave_types, attendance_statuses,
                                             // ticket_types, ticket_statuses, employee_account_types,
                                             // petty_cash_heads, payslip_item_types, gratuity_payout_statuses

            // 2. Holidays — no dependencies
            HolidaySeeder::class,

            // 3. Ticket request types — no dependencies
            TicketRequestTypesSeeder::class,

            // 4. Shift settings — depends on hr_attendance_statuses (seeded in step 1)
            ShiftSettingsSeeder::class,

            // 5. Super admin — depends on hr_roles table
            SuperAdminSeeder::class,
        ]);
    }
}
