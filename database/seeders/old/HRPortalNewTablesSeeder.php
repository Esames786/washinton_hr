<?php

namespace Database\Seeders\old;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HRPortalNewTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {



        // 5. employee_attendances
        DB::table('employee_attendances')->insert([
            [
                'employee_id' => 1,
                'shift_type_id' => 1,
                'attendance_date' => '2025-08-14',
                'check_in' => '09:00:00',
                'check_out' => '17:00:00',
                'attendance_status_id' => 1,
                'entry_weight' => 1.0,
                'calculated_salary' => 1166.67,
                'remarks' => 'On time',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'employee_id' => 2,
                'shift_type_id' => 1,
                'attendance_date' => '2025-08-14',
                'check_in' => '09:15:00',
                'check_out' => '17:00:00',
                'attendance_status_id' => 2,
                'entry_weight' => 0.5,
                'calculated_salary' => 583.33,
                'remarks' => 'Late',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 6. employee_breaks
        DB::table('employee_breaks')->insert([
            ['attendance_id' => 1, 'break_start' => '12:30:00', 'break_end' => '12:45:00', 'break_duration' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['attendance_id' => 2, 'break_start' => '13:00:00', 'break_end' => '13:20:00', 'break_duration' => 20, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 9. employee_daily_activities
        DB::table('employee_daily_activities')->insert([
            ['employee_id' => 1, 'activity_date' => '2025-08-14', 'activity_field_id' => 1, 'field_value' => 'Completed all tasks', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => 2, 'activity_date' => '2025-08-14', 'activity_field_id' => 2, 'field_value' => '20', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. role_commission_settings
        DB::table('role_commission_settings')->insert([
            ['role_id' => 1, 'commission_setting_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => 2, 'commission_setting_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. role_gratuity_settings
        DB::table('role_gratuity_settings')->insert([
            ['role_id' => 1, 'gratuity_setting_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => 2, 'gratuity_setting_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

//        // 10. employee_payslips
//        DB::table('employee_payslips')->insert([
//            ['employee_id' => 1, 'month_year' => '2025-08', 'basic_salary' => 35000, 'total_commission' => 3000, 'total_gratuity' => 1750, 'total_deductions' => 500, 'net_salary' => 36750, 'status_id' => 2, 'created_at' => now(), 'updated_at' => now()],
//            ['employee_id' => 2, 'month_year' => '2025-08', 'basic_salary' => 30000, 'total_commission' => 2000, 'total_gratuity' => 1500, 'total_deductions' => 800, 'net_salary' => 32700, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
//        ]);
//
//        // 11. payslip_items
//        DB::table('payslip_items')->insert([
//            ['payslip_id' => 1, 'item_type_id' => 1, 'description' => 'Basic Salary', 'amount' => 35000, 'created_at' => now(), 'updated_at' => now()],
//            ['payslip_id' => 1, 'item_type_id' => 1, 'description' => 'Commission', 'amount' => 3000, 'created_at' => now(), 'updated_at' => now()],
//            ['payslip_id' => 1, 'item_type_id' => 1, 'description' => 'Gratuity', 'amount' => 1750, 'created_at' => now(), 'updated_at' => now()],
//            ['payslip_id' => 1, 'item_type_id' => 2, 'description' => 'Late Penalty', 'amount' => 500, 'created_at' => now(), 'updated_at' => now()],
//        ]);
//
//        // 12. gratuity_balances
//        DB::table('gratuity_balances')->insert([
//            ['employee_id' => 1, 'month_year' => '2025-08', 'employee_contribution' => 1750, 'company_contribution' => 1750, 'total_contribution' => 3500, 'created_at' => now(), 'updated_at' => now()],
//            ['employee_id' => 2, 'month_year' => '2025-08', 'employee_contribution' => 1500, 'company_contribution' => 1500, 'total_contribution' => 3000, 'created_at' => now(), 'updated_at' => now()],
//        ]);
//
//        // 13. gratuity_payouts
//        DB::table('gratuity_payouts')->insert([
//            ['employee_id' => 1, 'total_balance' => 35000, 'payout_date' => Carbon::now(), 'remarks' => 'Resignation payout', 'created_at' => now(), 'updated_at' => now()],
//            ['employee_id' => 2, 'total_balance' => 30000, 'payout_date' => Carbon::now(), 'remarks' => 'Resignation payout', 'created_at' => now(), 'updated_at' => now()],
//        ]);
    }
}
