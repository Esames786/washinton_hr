<?php

namespace Database\Seeders\old;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoEmployeeSeerder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        //roles
        DB::table('hr_roles')->updateOrInsert(
            ['id' => 2],
            [
                'name'       => 'Junior Employee',
                'guard_name' => 'employee',
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        //employee
        DB::table('employees')->insert([
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => Hash::make('12345678'),
            'remember_token' => Str::random(10),
            'employee_code' => 'EMP-001',
            'department' => 'IT',
            'designation' => 'Software Engineer',
            'joining_date' => '2023-01-15',
            'resignation_date' => null,
            'working_hours' => '9 AM - 6 PM',
            'basic_salary' => 75000.00,
            'employment_type_id' => 1,
            'employee_status_id' => 1,
            'father_name' => 'Richard Doe',
            'mother_name' => 'Jane Doe',
            'cnic' => '35202-1234567-8',
            'dob' => '1995-06-10',
            'gender' => 'male',
            'marital_status' => 'single',
            'kids_count' => 0,
            'skills' => 'Laravel, React, MySQL',
            'phone' => '03001234567',
            'phone2' => '03111234567',
            'contact_person' => 'Michael Doe',
            'emergency_contact' => '03019876543',
            'address' => '123 Main Street',
            'city' => 'Karachi',
            'state' => 'Sindh',
            'country' => 'Pakistan',
            'role_id' => 2,
            'shift_id' => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employee_bank_details')->insert([
            'employee_id' => 1, // make sure this employee exists
            'bank_name' => 'Habib Bank Limited',
            'account_title' => 'John Doe',
            'account_number' => '001234567890',
            'iban' => 'PK36HABB0000123456789001',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        //role_activity_fields
        DB::table('role_activity_fields')->insert([
            ['role_id' => 2, 'activity_field_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => 2, 'activity_field_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
