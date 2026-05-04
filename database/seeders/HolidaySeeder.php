<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('holidays')->truncate();
        DB::table('holidays')->insert([
            [
                'id'=>1,
                'name'          => 'New Year’s Day',
                'holiday_date'  => null,
                'is_recurring'  => 1,
                'month'         => 1,
                'day'           => 1,
                'status'        => 1,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'id'=>2,
                'name'          => 'Pakistan Day',
                'holiday_date'  => null,
                'is_recurring'  => 1,
                'month'         => 3,
                'day'           => 23,
                'status'        => 1,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'id'=>3,
                'name'          => 'Labour Day',
                'holiday_date'  => null,
                'is_recurring'  => 1,
                'month'         => 5,
                'day'           => 1,
                'status'        => 1,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'id'=>4,
                'name'          => 'Independence Day',
                'holiday_date'  => null,
                'is_recurring'  => 1,
                'month'         => 8,
                'day'           => 14,
                'status'        => 1,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'id'=>5,
                'name'          => 'Defence Day',
                'holiday_date'  => null,
                'is_recurring'  => 1,
                'month'         => 9,
                'day'           => 6,
                'status'        => 1,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'id'=>6,
                'name'          => 'Iqbal Day',
                'holiday_date'  => null,
                'is_recurring'  => 1,
                'month'         => 11,
                'day'           => 9,
                'status'        => 1,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'id'=>7,
                'name'          => 'Quaid-e-Azam Day',
                'holiday_date'  => null,
                'is_recurring'  => 1,
                'month'         => 12,
                'day'           => 25,
                'status'        => 1,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
        ]);
    }
}
