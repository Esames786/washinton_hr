<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        // Insert Role
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
        DB::table('admins')->insert([
            'id'             => 1,
            'name'           => 'superadmin',
            'email'          => 'super@gmail.com',
            'password'       => Hash::make('12345678'), // Already hashed
            'status'         => 1,
            'remember_token' => null,
            'role_id'        => 1,
            'profile_path'   => null,
            'created_by'     => null,
            'updated_by'     => null,
            'created_at'     => '2025-08-11 19:32:02',
            'updated_at'     => '2025-08-11 19:32:03',
        ]);
    }
}
