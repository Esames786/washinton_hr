<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HrSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('employment_types')->truncate();
        DB::table('employment_types')->insert([
            ['id' => 1,'name' => 'Permanent', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2,'name' => 'Contract', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3,'name' => 'Probation', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('employee_statuses')->truncate();
        DB::table('employee_statuses')->insert([
            ['id' => 1,'name' => 'Active', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2,'name' => 'Inactive', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3,'name' => 'Terminated', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4,'name' => 'Resigned', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5,'name' => 'Training', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6,'name' => 'Trail', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7,'name' => 'Document Verification', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8,'name' => 'Pending Contract', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9,'name' => 'Management Approval', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10,'name' => 'Deployed', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('commission_types')->truncate();
        DB::table('commission_types')->insert([
            ['id' => 1,'name' => 'Percentage', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2,'name' => 'Fixed', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('commission_target_types')->truncate();
        DB::table('commission_target_types')->insert([
            ['id' => 1,'name' => 'Monthly', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2,'name' => 'Quarterly', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3,'name' => 'Yearly', 'created_at' => now(), 'updated_at' => now()],
        ]);



        // Payroll Statuses
        DB::table('payroll_statuses')->truncate();
        DB::table('payroll_statuses')->insert([
            ['id' => 1, 'name' => 'Process', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Draft', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Approved', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Paid', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Cancel', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Payroll Detail Statuses
        DB::table('payroll_detail_statuses')->truncate();
        DB::table('payroll_detail_statuses')->insert([
            ['id' => 1, 'name' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Approved', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Paid', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Gratuity Payout Statuses
        DB::table('gratuity_payout_statuses')->truncate();
        DB::table('gratuity_payout_statuses')->insert([
            ['id' => 1, 'name' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Approved', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Paid', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('leave_types')->truncate();
        DB::table('leave_types')->insert([
            ['id' => 1,'name' => 'Casual Leave', 'description' => 'Short casual leave', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2,'name' => 'Sick Leave', 'description' => 'Medical / Sick leave', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3,'name' => 'Annual Leave', 'description' => 'Yearly allocated leave', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4,'name' => 'Maternity Leave', 'description' => 'Leave for maternity period', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5,'name' => 'Unpaid Leave', 'description' => 'Leave without pay', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('payslip_item_types')->truncate();
        DB::table('payslip_item_types')->insert([
            ['id'=>1,'name' => 'Earning', 'created_at' => now(), 'updated_at' => now()],
            ['id'=>2,'name' => 'Deduction', 'created_at' => now(), 'updated_at' => now()],
            ['id'=>3,'name' => 'Info', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('attendance_statuses')->truncate();
        DB::table('attendance_statuses')->insert([
            ['id' => 1, 'name' => 'Late', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Present', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Half Day', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Early Exit', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Absent', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'Holiday', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'name' => 'Weekend', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'name' => 'Leave', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'name' => 'Quarter', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'name' => 'Early Halfday', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'name' => 'Early Quarter', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('ticket_types')->whereIn('id', [1, 2, 3])->delete();
        DB::table('ticket_types')->insert([
            ['id' => 1, 'name' => 'Attendance Request', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Leave Request', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Complaint', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('ticket_statuses')->truncate();
        DB::table('ticket_statuses')->insert([
            ['id'=>1,'name' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
            ['id'=>2,'name' => 'Approved', 'created_at' => now(), 'updated_at' => now()],
            ['id'=>3,'name' => 'Rejected', 'created_at' => now(), 'updated_at' => now()],
            ['id'=>4,'name' => 'Closed', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('employee_account_types')->truncate();
        DB::table('employee_account_types')->insert([
            ['id' => 1, 'name' => 'Salary Only', 'description' => 'Employee receives only salary', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Commission Only', 'description' => 'Employee receives only commission', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Salary + Commission', 'description' => 'Employee receives salary plus commission', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('petty_cash_heads')->whereIn('id', [1])->delete();
        DB::table('petty_cash_heads')->insert([
            ['id'=>1,'name' => 'Salaries','type' => 'expense','status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
