<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketRequestTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ticket_request_types')->insert([
            ['id'=>1,'name' => 'Attendance', 'created_at' => now(), 'updated_at' => now()],
            ['id'=>2,'name' => 'Leave', 'created_at' => now(), 'updated_at' => now()],
            ['id'=>3,'name' => 'Complaint', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
