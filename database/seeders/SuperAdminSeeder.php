<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        DB::table('hr_roles')->updateOrInsert(
            ['id' => 1],
            [
                'name'       => 'Super Admin',
                'guard_name' => 'admin',
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('hr_admins')->updateOrInsert(
            ['id' => 1],
            [
                'name'           => 'superadmin',
                'email'          => 'super@gmail.com',
                'password'       => Hash::make('12345678'),
                'status'         => 1,
                'remember_token' => null,
                'role_id'        => 1,
                'profile_path'   => null,
                'created_by'     => null,
                'updated_by'     => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]
        );
    }
}
