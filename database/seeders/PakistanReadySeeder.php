<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Pakistan-Ready Seeder for washinton_hr
 * Seeds: Departments, Designations, Roles (employee), Shifts, Tax Slabs,
 *        Gratuity Settings, Commission Settings, Document Settings,
 *        Currency Rates, Daily Activity Fields, and Sample Employees
 */
class PakistanReadySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ─────────────────────────────────────────────────────────────────
        // 1. DEPARTMENTS
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_departments')->truncate();
        DB::table('hr_departments')->insert([
            ['id'=>1,'name'=>'Order Taker (OT)','description'=>'Sales and order taking team','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'name'=>'Dispatch','description'=>'Dispatch and logistics team','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'name'=>'Human Resources','description'=>'HR and administration','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'name'=>'Management','description'=>'Managers and team leads','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'name'=>'Finance','description'=>'Accounts and payroll','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,'name'=>'IT','description'=>'Technology and systems','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,'name'=>'Customer Support','description'=>'Customer service and support','status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Departments seeded');

        // ─────────────────────────────────────────────────────────────────
        // 2. DESIGNATIONS
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_designations')->truncate();
        DB::table('hr_designations')->insert([
            ['id'=>1,'name'=>'Order Taker','department_id'=>1,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'name'=>'Senior Order Taker','department_id'=>1,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'name'=>'CSR','department_id'=>1,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'name'=>'Seller Agent','department_id'=>1,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'name'=>'Dispatcher','department_id'=>2,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,'name'=>'Senior Dispatcher','department_id'=>2,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,'name'=>'Logistics Coordinator','department_id'=>2,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>8,'name'=>'HR Executive','department_id'=>3,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>9,'name'=>'HR Manager','department_id'=>3,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>10,'name'=>'Team Lead','department_id'=>4,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>11,'name'=>'Manager','department_id'=>4,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>12,'name'=>'General Manager','department_id'=>4,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>13,'name'=>'Accountant','department_id'=>5,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>14,'name'=>'Finance Manager','department_id'=>5,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>15,'name'=>'IT Support','department_id'=>6,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>16,'name'=>'Software Developer','department_id'=>6,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>17,'name'=>'Support Agent','department_id'=>7,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Designations seeded');

        // ─────────────────────────────────────────────────────────────────
        // 3. EMPLOYEE ROLES (guard: employee)
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_roles')->whereIn('guard_name', ['employee'])->delete();
        DB::table('hr_roles')->insert([
            ['id'=>2,'name'=>'Order Taker','guard_name'=>'employee','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'name'=>'Dispatcher','guard_name'=>'employee','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'name'=>'Manager','guard_name'=>'employee','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'name'=>'HR Executive','guard_name'=>'employee','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,'name'=>'Accountant','guard_name'=>'employee','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,'name'=>'IT Staff','guard_name'=>'employee','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>8,'name'=>'Support Agent','guard_name'=>'employee','status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Employee Roles seeded');

        // ─────────────────────────────────────────────────────────────────
        // 4. SHIFT TYPES + ATTENDANCE RULES
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_shift_attendance_rules')->truncate();
        DB::table('hr_shift_types')->truncate();

        DB::table('hr_shift_types')->insert([
            ['id'=>1,'name'=>'Morning','shift_start'=>'09:00:00','shift_end'=>'17:00:00','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'name'=>'Evening','shift_start'=>'14:00:00','shift_end'=>'22:00:00','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'name'=>'Night','shift_start'=>'20:00:00','shift_end'=>'04:00:00','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'name'=>'General','shift_start'=>'10:00:00','shift_end'=>'18:00:00','status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);

        // Attendance rules for each shift
        // attendance_status_id: 1=Late, 2=Present, 3=Half Day, 4=Early Exit, 5=Absent, 9=Quarter
        $shiftRules = [];
        foreach ([1,2,3,4] as $shiftId) {
            $base = match($shiftId) {
                1 => ['09:30:00','10:00:00','13:00:00','15:00:00'],
                2 => ['14:30:00','15:00:00','18:00:00','20:00:00'],
                3 => ['20:30:00','21:00:00','00:00:00','02:00:00'],
                4 => ['10:30:00','11:00:00','14:00:00','16:00:00'],
            };
            $shiftRules[] = ['shift_type_id'=>$shiftId,'attendance_status_id'=>1,'entry_time'=>$base[0],'entry_weight'=>80,'status'=>1,'created_at'=>$now,'updated_at'=>$now];
            $shiftRules[] = ['shift_type_id'=>$shiftId,'attendance_status_id'=>2,'entry_time'=>$base[1],'entry_weight'=>100,'status'=>1,'created_at'=>$now,'updated_at'=>$now];
            $shiftRules[] = ['shift_type_id'=>$shiftId,'attendance_status_id'=>3,'entry_time'=>$base[2],'entry_weight'=>50,'status'=>1,'created_at'=>$now,'updated_at'=>$now];
            $shiftRules[] = ['shift_type_id'=>$shiftId,'attendance_status_id'=>4,'entry_time'=>$base[3],'entry_weight'=>75,'status'=>1,'created_at'=>$now,'updated_at'=>$now];
            $shiftRules[] = ['shift_type_id'=>$shiftId,'attendance_status_id'=>5,'entry_time'=>null,'entry_weight'=>0,'status'=>1,'created_at'=>$now,'updated_at'=>$now];
            $shiftRules[] = ['shift_type_id'=>$shiftId,'attendance_status_id'=>9,'entry_time'=>$base[2],'entry_weight'=>25,'status'=>1,'created_at'=>$now,'updated_at'=>$now];
        }
        DB::table('hr_shift_attendance_rules')->insert($shiftRules);
        $this->command->info('✓ Shifts + Attendance Rules seeded');

        // ─────────────────────────────────────────────────────────────────
        // 5. PAKISTAN TAX SLABS (FY 2024-25)
        //    Based on FBR income tax slabs for salaried individuals
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_tax_slab_settings')->truncate();
        DB::table('hr_tax_slab_settings')->insert([
            // Exempt (up to 50,000/month = 600,000/year)
            ['id'=>1,'name'=>'Tax Exempt (Up to PKR 50,000/month)','type'=>'percentage','rate'=>0,'global_cap'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            // 2.5% (50,001 - 100,000/month)
            ['id'=>2,'name'=>'2.5% Slab (PKR 50,001 - 100,000/month)','type'=>'percentage','rate'=>2.5,'global_cap'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            // 12.5% (100,001 - 200,000/month)
            ['id'=>3,'name'=>'12.5% Slab (PKR 100,001 - 200,000/month)','type'=>'percentage','rate'=>12.5,'global_cap'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            // 22.5% (200,001 - 300,000/month)
            ['id'=>4,'name'=>'22.5% Slab (PKR 200,001 - 300,000/month)','type'=>'percentage','rate'=>22.5,'global_cap'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            // 27.5% (300,001 - 500,000/month)
            ['id'=>5,'name'=>'27.5% Slab (PKR 300,001 - 500,000/month)','type'=>'percentage','rate'=>27.5,'global_cap'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            // 35% (Above 500,000/month)
            ['id'=>6,'name'=>'35% Slab (Above PKR 500,000/month)','type'=>'percentage','rate'=>35,'global_cap'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            // Fixed flat deductions
            ['id'=>7,'name'=>'Fixed PKR 1,000/month','type'=>'fixed','rate'=>1000,'global_cap'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>8,'name'=>'Fixed PKR 2,500/month','type'=>'fixed','rate'=>2500,'global_cap'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>9,'name'=>'Fixed PKR 5,000/month','type'=>'fixed','rate'=>5000,'global_cap'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Pakistan Tax Slabs seeded (FBR FY 2024-25)');

        // ─────────────────────────────────────────────────────────────────
        // 6. GRATUITY SETTINGS
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_gratuity_settings')->truncate();
        DB::table('hr_gratuity_settings')->insert([
            ['id'=>1,'name'=>'Standard Gratuity (PF)','is_pf'=>1,'employee_contribution_percentage'=>8.33,'company_contribution_percentage'=>8.33,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'name'=>'Enhanced Gratuity','is_pf'=>1,'employee_contribution_percentage'=>10,'company_contribution_percentage'=>12,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'name'=>'No Gratuity','is_pf'=>0,'employee_contribution_percentage'=>0,'company_contribution_percentage'=>0,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Gratuity Settings seeded');

        // ─────────────────────────────────────────────────────────────────
        // 7. COMMISSION SETTINGS
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_commission_settings')->truncate();
        DB::table('hr_commission_settings')->insert([
            ['id'=>1,'name'=>'Standard 5% Commission','commission_type_id'=>1,'value'=>5,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'name'=>'Standard 10% Commission','commission_type_id'=>1,'value'=>10,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'name'=>'Fixed PKR 5,000 Commission','commission_type_id'=>2,'value'=>5000,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'name'=>'Fixed PKR 10,000 Commission','commission_type_id'=>2,'value'=>10000,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'name'=>'Performance 15% Commission','commission_type_id'=>1,'value'=>15,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Commission Settings seeded');

        // ─────────────────────────────────────────────────────────────────
        // 8. DOCUMENT SETTINGS
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_document_settings')->truncate();
        DB::table('hr_document_settings')->insert([
            ['id'=>1,'title'=>'CNIC (National ID)','description'=>'Computerized National Identity Card','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'title'=>'Educational Certificate','description'=>'Highest degree or diploma','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'title'=>'Experience Letter','description'=>'Previous employer experience letter','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'title'=>'Passport','description'=>'Valid passport copy','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'title'=>'Bank Account Details','description'=>'Bank account verification document','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,'title'=>'Medical Certificate','description'=>'Fitness certificate from doctor','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,'title'=>'Police Clearance','description'=>'Character certificate from police','status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Document Settings seeded');

        // ─────────────────────────────────────────────────────────────────
        // 9. CURRENCY EXCHANGE RATE (USD to PKR)
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_currency_rates')->truncate();
        DB::table('hr_currency_rates')->insert([
            ['id'=>1,'from_currency'=>'USD','to_currency'=>'PKR','rate'=>278.50,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'from_currency'=>'GBP','to_currency'=>'PKR','rate'=>352.00,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'from_currency'=>'EUR','to_currency'=>'PKR','rate'=>300.00,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'from_currency'=>'SAR','to_currency'=>'PKR','rate'=>74.20,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'from_currency'=>'AED','to_currency'=>'PKR','rate'=>75.80,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Currency Rates seeded');

        // ─────────────────────────────────────────────────────────────────
        // 10. DAILY ACTIVITY FIELDS (per role)
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_daily_activity_fields')->truncate();
        $activityFields = [];
        $fieldDefs = [
            ['field_name'=>'Calls Made','field_type'=>'number','is_required'=>1],
            ['field_name'=>'Orders Booked','field_type'=>'number','is_required'=>1],
            ['field_name'=>'Follow Ups','field_type'=>'number','is_required'=>0],
            ['field_name'=>'Dispatches Done','field_type'=>'number','is_required'=>1],
            ['field_name'=>'Issues Resolved','field_type'=>'number','is_required'=>0],
            ['field_name'=>'Daily Report','field_type'=>'text','is_required'=>0],
        ];
        $id = 1;
        foreach ([2,3,4,5,6,7,8] as $roleId) {
            foreach ($fieldDefs as $field) {
                $activityFields[] = array_merge($field, ['id'=>$id++,'role_id'=>$roleId,'status'=>1,'created_at'=>$now,'updated_at'=>$now]);
            }
        }
        DB::table('hr_daily_activity_fields')->insert($activityFields);
        $this->command->info('✓ Daily Activity Fields seeded');

        // ─────────────────────────────────────────────────────────────────
        // 11. SAMPLE EMPLOYEES (5 employees across departments)
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_employee_working_days')->truncate();
        DB::table('hr_employee_assign_leaves')->truncate();
        DB::table('hr_employees')->truncate();

        $employees = [
            [
                'id'=>1,'full_name'=>'Ahmed Ali','email'=>'ahmed.ali@daydispatch.com',
                'password'=>Hash::make('password123'),'employee_code'=>'EMP-001',
                'cnic'=>'42101-1234567-1','phone'=>'03001234567','gender'=>'male',
                'dob'=>'1995-03-15','joining_date'=>'2023-01-01',
                'department_id'=>1,'designation_id'=>1,'role_id'=>2,
                'shift_id'=>1,'employment_type_id'=>1,'employee_status_id'=>1,
                'account_type_id'=>1,'basic_salary'=>60000,'is_taxable'=>1,
                'tax_slab_setting_id'=>2,'gratuity_id'=>1,'valid_gratuity_date'=>'2024-01-01',
                'marital_status'=>'single','address'=>'House 12, Block A, Gulshan-e-Iqbal, Karachi',
                'city'=>'Karachi','state'=>'Sindh','country'=>'Pakistan',
                'created_at'=>$now,'updated_at'=>$now,
            ],
            [
                'id'=>2,'full_name'=>'Sara Khan','email'=>'sara.khan@daydispatch.com',
                'password'=>Hash::make('password123'),'employee_code'=>'EMP-002',
                'cnic'=>'42201-2345678-2','phone'=>'03112345678','gender'=>'female',
                'dob'=>'1997-07-22','joining_date'=>'2023-03-15',
                'department_id'=>2,'designation_id'=>5,'role_id'=>3,
                'shift_id'=>2,'employment_type_id'=>1,'employee_status_id'=>1,
                'account_type_id'=>1,'basic_salary'=>75000,'is_taxable'=>1,
                'tax_slab_setting_id'=>2,'gratuity_id'=>1,'valid_gratuity_date'=>'2024-03-15',
                'marital_status'=>'single','address'=>'Flat 5, DHA Phase 2, Lahore',
                'city'=>'Lahore','state'=>'Punjab','country'=>'Pakistan',
                'created_at'=>$now,'updated_at'=>$now,
            ],
            [
                'id'=>3,'full_name'=>'Usman Tariq','email'=>'usman.tariq@daydispatch.com',
                'password'=>Hash::make('password123'),'employee_code'=>'EMP-003',
                'cnic'=>'35202-3456789-3','phone'=>'03213456789','gender'=>'male',
                'dob'=>'1990-11-05','joining_date'=>'2022-06-01',
                'department_id'=>4,'designation_id'=>11,'role_id'=>4,
                'shift_id'=>4,'employment_type_id'=>1,'employee_status_id'=>1,
                'account_type_id'=>1,'basic_salary'=>150000,'is_taxable'=>1,
                'tax_slab_setting_id'=>3,'gratuity_id'=>2,'valid_gratuity_date'=>'2023-06-01',
                'marital_status'=>'married','address'=>'Plot 22, F-7/2, Islamabad',
                'city'=>'Islamabad','state'=>'ICT','country'=>'Pakistan',
                'created_at'=>$now,'updated_at'=>$now,
            ],
            [
                'id'=>4,'full_name'=>'Fatima Noor','email'=>'fatima.noor@daydispatch.com',
                'password'=>Hash::make('password123'),'employee_code'=>'EMP-004',
                'cnic'=>'42301-4567890-4','phone'=>'03334567890','gender'=>'female',
                'dob'=>'1993-05-18','joining_date'=>'2023-07-01',
                'department_id'=>3,'designation_id'=>8,'role_id'=>5,
                'shift_id'=>1,'employment_type_id'=>1,'employee_status_id'=>1,
                'account_type_id'=>1,'basic_salary'=>55000,'is_taxable'=>0,
                'tax_slab_setting_id'=>1,'gratuity_id'=>1,'valid_gratuity_date'=>'2024-07-01',
                'marital_status'=>'single','address'=>'House 8, Gulberg III, Lahore',
                'city'=>'Lahore','state'=>'Punjab','country'=>'Pakistan',
                'created_at'=>$now,'updated_at'=>$now,
            ],
            [
                'id'=>5,'full_name'=>'Bilal Hussain','email'=>'bilal.hussain@daydispatch.com',
                'password'=>Hash::make('password123'),'employee_code'=>'EMP-005',
                'cnic'=>'42401-5678901-5','phone'=>'03455678901','gender'=>'male',
                'dob'=>'1992-09-30','joining_date'=>'2022-11-01',
                'department_id'=>1,'designation_id'=>2,'role_id'=>2,
                'shift_id'=>3,'employment_type_id'=>1,'employee_status_id'=>1,
                'account_type_id'=>2,'basic_salary'=>1,'is_taxable'=>0,
                'tax_slab_setting_id'=>null,'gratuity_id'=>3,'valid_gratuity_date'=>null,
                'commission_id'=>1,'marital_status'=>'married',
                'address'=>'Flat 3, Clifton Block 5, Karachi',
                'city'=>'Karachi','state'=>'Sindh','country'=>'Pakistan',
                'created_at'=>$now,'updated_at'=>$now,
            ],
        ];

        foreach ($employees as $emp) {
            DB::table('hr_employees')->insert($emp);

            // Working days (Mon-Fri = working, Sat-Sun = off)
            $workingDays = [];
            for ($day = 0; $day <= 6; $day++) {
                $workingDays[] = [
                    'employee_id' => $emp['id'],
                    'day_of_week' => $day,
                    'is_working'  => in_array($day, [1,2,3,4,5]) ? 1 : 0,
                    'created_by'  => 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
            DB::table('hr_employee_working_days')->insert($workingDays);

            // Assign leaves
            $leaveAssignments = [
                ['leave_type_id'=>1,'assigned_quota'=>12,'valid_from'=>'2024-01-01','valid_to'=>'2024-12-31'],
                ['leave_type_id'=>2,'assigned_quota'=>10,'valid_from'=>'2024-01-01','valid_to'=>'2024-12-31'],
                ['leave_type_id'=>3,'assigned_quota'=>14,'valid_from'=>'2024-01-01','valid_to'=>'2024-12-31'],
            ];
            foreach ($leaveAssignments as $leave) {
                DB::table('hr_employee_assign_leaves')->insert(array_merge($leave, [
                    'employee_id' => $emp['id'],
                    'status'      => 1,
                    'created_by'  => 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]));
            }
        }
        $this->command->info('✓ 5 Sample Employees seeded with working days and leave assignments');

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->info('  Pakistan Ready Seeder COMPLETE!');
        $this->command->info('  Run: php artisan db:seed --class=PakistanReadySeeder');
        $this->command->info('═══════════════════════════════════════════════════');
    }
}