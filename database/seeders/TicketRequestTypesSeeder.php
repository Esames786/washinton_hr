<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketRequestTypesSeeder extends Seeder
{
    public function run()
    {
        DB::table('hr_ticket_request_types')->insert([
            ['id' => 1, 'name' => 'Attendance', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Leave',      'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Complaint',  'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
