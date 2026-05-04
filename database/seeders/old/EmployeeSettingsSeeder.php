<?php

namespace Database\Seeders\old;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSettingsSeeder extends Seeder
{
    public function run(): void
    {

        // 1. gratuity_settings
        DB::table('gratuity_settings')->insert([
            [
                'title' => 'Standard Gratuity Plan',
                'eligibility_years' => 5,
                'employee_contribution_percentage' => 5.00,
                'company_contribution_percentage' => 5.00,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Instant Gratuity',
                'eligibility_years' => 0,
                'employee_contribution_percentage' => 5.00,
                'company_contribution_percentage' => 5.00,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Commission Types
        DB::table('commission_types')->insert([
            ['id' => 1, 'name' => 'Percentage'],
            ['id' => 2, 'name' => 'Fixed'],
        ]);

        // Commission Target Types
        DB::table('commission_target_types')->insert([
            ['id' => 1, 'name' => 'Monthly'],
            ['id' => 2, 'name' => 'Quarterly'],
            ['id' => 3, 'name' => 'Yearly'],
        ]);

        // Commission Settings
        DB::table('commission_settings')->insert([
            [
                'title' => 'Sales Commission',
                'description' => '10% on monthly sales',
                'commission_type_id' => 1,
                'value' => 10.00,
                'target_type_id' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Delivery Bonus',
                'description' => 'Flat reward for delivery staff',
                'commission_type_id' => 2,
                'value' => 2000,
                'target_type_id' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Document Settings
        DB::table('document_settings')->insert([
            [
                'title' => 'CNIC',
                'is_required' => true,
                'description' => 'National ID Card',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Driving License',
                'is_required' => false,
                'description' => 'For drivers only',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Resume',
                'is_required' => true,
                'description' => 'Updated resume',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 7. daily_activity_fields
        DB::table('daily_activity_fields')->insert([
            ['id'=>1,'name' => 'Facebook Post Count', 'field_type' => 'text', 'options' => null, 'is_required' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id'=>2,'name' => 'Prove of FB Post', 'field_type' => 'file', 'options' => null, 'is_required' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

    }
}
