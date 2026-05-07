<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Pakistan-Ready Seeder for washinton_hr
 * All columns verified against actual migration files.
 */
class PakistanReadySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ─────────────────────────────────────────────────────────────────
        // 1. DEPARTMENTS
        // Columns: id, name, status, created_at, updated_at
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_departments')->truncate();
        DB::table('hr_departments')->insert([
            ['id'=>1,'name'=>'Order Taker (OT)','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'name'=>'Dispatch','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'name'=>'Human Resources','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'name'=>'Management','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'name'=>'Finance','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,'name'=>'IT','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,'name'=>'Customer Support','status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Departments seeded');

        // ─────────────────────────────────────────────────────────────────
        // 2. DESIGNATIONS
        // Columns: id, name, status, created_at, updated_at
        // (no department_id — that column does not exist in migration)
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_designations')->truncate();
        DB::table('hr_designations')->insert([
            ['id'=>1,'name'=>'Order Taker','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'name'=>'Senior Order Taker','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'name'=>'CSR','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'name'=>'Seller Agent','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'name'=>'Dispatcher','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,'name'=>'Senior Dispatcher','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,'name'=>'Logistics Coordinator','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>8,'name'=>'HR Executive','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>9,'name'=>'HR Manager','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>10,'name'=>'Team Lead','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>11,'name'=>'Manager','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>12,'name'=>'General Manager','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>13,'name'=>'Accountant','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>14,'name'=>'Finance Manager','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>15,'name'=>'IT Support','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>16,'name'=>'Software Developer','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>17,'name'=>'Support Agent','status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Designations seeded');

        // ─────────────────────────────────────────────────────────────────
        // 3. EMPLOYEE ROLES (guard: employee)
        // Columns: id, name, guard_name, status, created_at, updated_at
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
        // hr_shift_types: id, name, shift_start, shift_end, status, timestamps
        // hr_shift_attendance_rules: shift_type_id, attendance_status_id,
        //   entry_time, entry_weight, status, timestamps
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_shift_attendance_rules')->truncate();
        DB::table('hr_shift_types')->truncate();

        DB::table('hr_shift_types')->insert([
            ['id'=>1,'name'=>'Morning','shift_start'=>'09:00:00','shift_end'=>'17:00:00','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'name'=>'Evening','shift_start'=>'14:00:00','shift_end'=>'22:00:00','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'name'=>'Night','shift_start'=>'20:00:00','shift_end'=>'04:00:00','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'name'=>'General','shift_start'=>'10:00:00','shift_end'=>'18:00:00','status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);

        $shiftRules = [];
        foreach ([1,2,3,4] as $shiftId) {
            $base = match($shiftId) {
                1 => ['09:30:00','10:00:00','13:00:00','15:00:00'],
                2 => ['14:30:00','15:00:00','18:00:00','20:00:00'],
                3 => ['20:30:00','21:00:00','00:00:00','02:00:00'],
                4 => ['10:30:00','11:00:00','14:00:00','16:00:00'],
            };
            // attendance_status_id: 1=Late, 2=Present, 3=Half Day, 4=Early Exit, 5=Absent, 9=Quarter
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
        // 5. PAKISTAN TAX SLABS (FBR FY 2024-25)
        // Columns: id, title, min_income, max_income, rate, type,
        //          global_cap, description, status, created_by, updated_by, timestamps
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_tax_slab_settings')->truncate();
        DB::table('hr_tax_slab_settings')->insert([
            ['id'=>1,'title'=>'Tax Exempt','min_income'=>0,'max_income'=>50000,'rate'=>0,'type'=>'percentage','global_cap'=>null,'description'=>'Up to PKR 50,000/month — no tax (FBR FY 2024-25)','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'title'=>'2.5% Slab','min_income'=>50001,'max_income'=>100000,'rate'=>2.5,'type'=>'percentage','global_cap'=>null,'description'=>'PKR 50,001 – 100,000/month','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'title'=>'12.5% Slab','min_income'=>100001,'max_income'=>200000,'rate'=>12.5,'type'=>'percentage','global_cap'=>null,'description'=>'PKR 100,001 – 200,000/month','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'title'=>'22.5% Slab','min_income'=>200001,'max_income'=>300000,'rate'=>22.5,'type'=>'percentage','global_cap'=>null,'description'=>'PKR 200,001 – 300,000/month','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'title'=>'27.5% Slab','min_income'=>300001,'max_income'=>500000,'rate'=>27.5,'type'=>'percentage','global_cap'=>null,'description'=>'PKR 300,001 – 500,000/month','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,'title'=>'35% Slab','min_income'=>500001,'max_income'=>null,'rate'=>35,'type'=>'percentage','global_cap'=>null,'description'=>'Above PKR 500,000/month','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,'title'=>'Fixed PKR 1,000/month','min_income'=>0,'max_income'=>null,'rate'=>1000,'type'=>'fixed','global_cap'=>null,'description'=>'Flat fixed deduction','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>8,'title'=>'Fixed PKR 2,500/month','min_income'=>0,'max_income'=>null,'rate'=>2500,'type'=>'fixed','global_cap'=>null,'description'=>'Flat fixed deduction','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>9,'title'=>'Fixed PKR 5,000/month','min_income'=>0,'max_income'=>null,'rate'=>5000,'type'=>'fixed','global_cap'=>null,'description'=>'Flat fixed deduction','status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Pakistan Tax Slabs seeded (FBR FY 2024-25)');

        // ─────────────────────────────────────────────────────────────────
        // 6. GRATUITY SETTINGS
        // Columns: id, title, description, employee_contribution_percentage,
        //          company_contribution_percentage, eligibility_years,
        //          status, is_pf (added via alter), created_by, updated_by, timestamps
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_gratuity_settings')->truncate();
        DB::table('hr_gratuity_settings')->insert([
            ['id'=>1,'title'=>'Standard Gratuity (PF)','description'=>'Standard provident fund — 8.33% each side','employee_contribution_percentage'=>8.33,'company_contribution_percentage'=>8.33,'eligibility_years'=>1,'is_pf'=>1,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'title'=>'Enhanced Gratuity','description'=>'Enhanced PF with higher company contribution','employee_contribution_percentage'=>10,'company_contribution_percentage'=>12,'eligibility_years'=>1,'is_pf'=>1,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'title'=>'No Gratuity','description'=>'Commission-based employees — no gratuity','employee_contribution_percentage'=>0,'company_contribution_percentage'=>0,'eligibility_years'=>0,'is_pf'=>0,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Gratuity Settings seeded');

        // ─────────────────────────────────────────────────────────────────
        // 7. COMMISSION SETTINGS
        // Columns: id, title, description, commission_type_id, value,
        //          target_type_id, status, created_by, updated_by, timestamps
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_commission_settings')->truncate();
        DB::table('hr_commission_settings')->insert([
            ['id'=>1,'title'=>'Standard 5% Commission','description'=>'5% of order profit','commission_type_id'=>1,'value'=>5,'target_type_id'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'title'=>'Standard 10% Commission','description'=>'10% of order profit','commission_type_id'=>1,'value'=>10,'target_type_id'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'title'=>'Fixed PKR 5,000','description'=>'Fixed PKR 5,000 per month','commission_type_id'=>2,'value'=>5000,'target_type_id'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'title'=>'Fixed PKR 10,000','description'=>'Fixed PKR 10,000 per month','commission_type_id'=>2,'value'=>10000,'target_type_id'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'title'=>'Performance 15% Commission','description'=>'15% for high performers','commission_type_id'=>1,'value'=>15,'target_type_id'=>null,'status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Commission Settings seeded');

        // ─────────────────────────────────────────────────────────────────
        // 8. DOCUMENT SETTINGS
        // Columns: id, title, is_required, description, input_type,
        //          status, created_by, updated_by, timestamps
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_document_settings')->truncate();
        DB::table('hr_document_settings')->insert([
            ['id'=>1,'title'=>'CNIC (National ID)','is_required'=>1,'description'=>'Computerized National Identity Card','input_type'=>'file','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>2,'title'=>'Educational Certificate','is_required'=>1,'description'=>'Highest degree or diploma','input_type'=>'file','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>3,'title'=>'Experience Letter','is_required'=>0,'description'=>'Previous employer experience letter','input_type'=>'file','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>4,'title'=>'Passport','is_required'=>0,'description'=>'Valid passport copy','input_type'=>'file','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>5,'title'=>'Bank Account Details','is_required'=>1,'description'=>'Bank account verification document','input_type'=>'file','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>6,'title'=>'Medical Certificate','is_required'=>0,'description'=>'Fitness certificate from doctor','input_type'=>'file','status'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['id'=>7,'title'=>'Police Clearance','is_required'=>0,'description'=>'Character certificate from police','input_type'=>'file','status'=>1,'created_at'=>$now,'updated_at'=>$now],
        ]);
        $this->command->info('✓ Document Settings seeded');

        // ─────────────────────────────────────────────────────────────────
        // 9. CURRENCY EXCHANGE RATES
        // Columns: id, from_currency, to_currency, rate, status, timestamps
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
        // 10. DAILY ACTIVITY FIELDS + ROLE ASSIGNMENTS
        // hr_daily_activity_fields: id, name, field_type, options,
        //   is_required, status, created_by, updated_by, timestamps
        // hr_role_activity_fields: id, role_id, activity_field_id, timestamps
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_role_activity_fields')->truncate();
        DB::table('hr_daily_activity_fields')->truncate();

        $fieldDefs = [
            ['id'=>1,'name'=>'Calls Made','field_type'=>'number','is_required'=>1],
            ['id'=>2,'name'=>'Orders Booked','field_type'=>'number','is_required'=>1],
            ['id'=>3,'name'=>'Follow Ups','field_type'=>'number','is_required'=>0],
            ['id'=>4,'name'=>'Dispatches Done','field_type'=>'number','is_required'=>1],
            ['id'=>5,'name'=>'Issues Resolved','field_type'=>'number','is_required'=>0],
            ['id'=>6,'name'=>'Daily Report','field_type'=>'textarea','is_required'=>0],
        ];

        foreach ($fieldDefs as $field) {
            DB::table('hr_daily_activity_fields')->insert(array_merge($field, [
                'options'    => null,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // Assign all fields to all employee roles (role ids 2–8)
        $roleFieldPivots = [];
        $pivotId = 1;
        foreach ([2,3,4,5,6,7,8] as $roleId) {
            foreach ($fieldDefs as $field) {
                $roleFieldPivots[] = [
                    'id'               => $pivotId++,
                    'role_id'          => $roleId,
                    'activity_field_id'=> $field['id'],
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }
        DB::table('hr_role_activity_fields')->insert($roleFieldPivots);
        $this->command->info('✓ Daily Activity Fields + Role Assignments seeded');

        // ─────────────────────────────────────────────────────────────────
        // 11. SAMPLE EMPLOYEES (5 employees)
        // Final columns after all migrations:
        //   full_name, email, password, employee_code,
        //   department_id, designation_id, joining_date, basic_salary,
        //   employment_type_id, employee_status_id,
        //   cnic, dob, gender, marital_status, phone,
        //   address, city, state, country,
        //   role_id, shift_id,
        //   gratuity_id, commission_id, valid_gratuity_date,
        //   is_taxable, tax_slab_setting_id, account_type_id
        // ─────────────────────────────────────────────────────────────────
        DB::table('hr_employee_working_days')->truncate();
        DB::table('hr_employee_assign_leaves')->truncate();
        DB::table('hr_employees')->truncate();

        $employees = [
            [
                'id'=>1,'full_name'=>'Ahmed Ali',
                'email'=>'ahmed.ali@daydispatch.com',
                'password'=>Hash::make('password123'),
                'employee_code'=>'EMP-001',
                'cnic'=>'42101-1234567-1','phone'=>'03001234567',
                'gender'=>'male','dob'=>'1995-03-15','joining_date'=>'2023-01-01',
                'department_id'=>1,'designation_id'=>1,'role_id'=>2,
                'shift_id'=>1,'employment_type_id'=>1,'employee_status_id'=>1,
                'account_type_id'=>1,'basic_salary'=>60000,
                'is_taxable'=>1,'tax_slab_setting_id'=>2,
                'gratuity_id'=>1,'valid_gratuity_date'=>'2024-01-01',
                'marital_status'=>'single',
                'address'=>'House 12, Block A, Gulshan-e-Iqbal, Karachi',
                'city'=>'Karachi','state'=>'Sindh','country'=>'Pakistan',
                'created_at'=>$now,'updated_at'=>$now,
            ],
            [
                'id'=>2,'full_name'=>'Sara Khan',
                'email'=>'sara.khan@daydispatch.com',
                'password'=>Hash::make('password123'),
                'employee_code'=>'EMP-002',
                'cnic'=>'42201-2345678-2','phone'=>'03112345678',
                'gender'=>'female','dob'=>'1997-07-22','joining_date'=>'2023-03-15',
                'department_id'=>2,'designation_id'=>5,'role_id'=>3,
                'shift_id'=>2,'employment_type_id'=>1,'employee_status_id'=>1,
                'account_type_id'=>1,'basic_salary'=>75000,
                'is_taxable'=>1,'tax_slab_setting_id'=>2,
                'gratuity_id'=>1,'valid_gratuity_date'=>'2024-03-15',
                'marital_status'=>'single',
                'address'=>'Flat 5, DHA Phase 2, Lahore',
                'city'=>'Lahore','state'=>'Punjab','country'=>'Pakistan',
                'created_at'=>$now,'updated_at'=>$now,
            ],
            [
                'id'=>3,'full_name'=>'Usman Tariq',
                'email'=>'usman.tariq@daydispatch.com',
                'password'=>Hash::make('password123'),
                'employee_code'=>'EMP-003',
                'cnic'=>'35202-3456789-3','phone'=>'03213456789',
                'gender'=>'male','dob'=>'1990-11-05','joining_date'=>'2022-06-01',
                'department_id'=>4,'designation_id'=>11,'role_id'=>4,
                'shift_id'=>4,'employment_type_id'=>1,'employee_status_id'=>1,
                'account_type_id'=>1,'basic_salary'=>150000,
                'is_taxable'=>1,'tax_slab_setting_id'=>3,
                'gratuity_id'=>2,'valid_gratuity_date'=>'2023-06-01',
                'marital_status'=>'married',
                'address'=>'Plot 22, F-7/2, Islamabad',
                'city'=>'Islamabad','state'=>'ICT','country'=>'Pakistan',
                'created_at'=>$now,'updated_at'=>$now,
            ],
            [
                'id'=>4,'full_name'=>'Fatima Noor',
                'email'=>'fatima.noor@daydispatch.com',
                'password'=>Hash::make('password123'),
                'employee_code'=>'EMP-004',
                'cnic'=>'42301-4567890-4','phone'=>'03334567890',
                'gender'=>'female','dob'=>'1993-05-18','joining_date'=>'2023-07-01',
                'department_id'=>3,'designation_id'=>8,'role_id'=>5,
                'shift_id'=>1,'employment_type_id'=>1,'employee_status_id'=>1,
                'account_type_id'=>1,'basic_salary'=>55000,
                'is_taxable'=>0,'tax_slab_setting_id'=>1,
                'gratuity_id'=>1,'valid_gratuity_date'=>'2024-07-01',
                'marital_status'=>'single',
                'address'=>'House 8, Gulberg III, Lahore',
                'city'=>'Lahore','state'=>'Punjab','country'=>'Pakistan',
                'created_at'=>$now,'updated_at'=>$now,
            ],
            [
                'id'=>5,'full_name'=>'Bilal Hussain',
                'email'=>'bilal.hussain@daydispatch.com',
                'password'=>Hash::make('password123'),
                'employee_code'=>'EMP-005',
                'cnic'=>'42401-5678901-5','phone'=>'03455678901',
                'gender'=>'male','dob'=>'1992-09-30','joining_date'=>'2022-11-01',
                'department_id'=>1,'designation_id'=>2,'role_id'=>2,
                'shift_id'=>3,'employment_type_id'=>1,'employee_status_id'=>1,
                'account_type_id'=>2,'basic_salary'=>1,
                'is_taxable'=>0,'tax_slab_setting_id'=>null,
                'gratuity_id'=>3,'valid_gratuity_date'=>null,
                'commission_id'=>1,'marital_status'=>'married',
                'address'=>'Flat 3, Clifton Block 5, Karachi',
                'city'=>'Karachi','state'=>'Sindh','country'=>'Pakistan',
                'created_at'=>$now,'updated_at'=>$now,
            ],
        ];

        foreach ($employees as $emp) {
            DB::table('hr_employees')->insert($emp);

            // Working days: Mon–Fri working, Sat–Sun off
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

            // Assign leaves (leave_type_id 1,2,3 must exist from HrSettingSeeder)
            foreach ([
                ['leave_type_id'=>1,'assigned_quota'=>12],
                ['leave_type_id'=>2,'assigned_quota'=>10],
                ['leave_type_id'=>3,'assigned_quota'=>14],
            ] as $leave) {
                DB::table('hr_employee_assign_leaves')->insert(array_merge($leave, [
                    'employee_id' => $emp['id'],
                    'valid_from'  => '2024-01-01',
                    'valid_to'    => '2024-12-31',
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
        $this->command->info('═══════════════════════════════════════════════════');
    }
}
