<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoAttendanceSeeder extends Seeder
{
    public function run()
    {
        $employeeId   = 3;
        $basicSalary  = 1; // fixed for demo
        $dailySalary  = $basicSalary / 30;

        $startDate = Carbon::create(2025, 9, 1); // August 2025
        $endDate   = $startDate->copy()->endOfMonth();

        // Possible statuses (status_id => [weight, label])
        $statuses = [
            2 => ['weight' => 0, 'label' => 'Present'],
            5  => ['weight' => 100,   'label' => 'Absent'],
            1  => ['weight' => 75,  'label' => 'Late'],
            4  => ['weight' => 50,  'label' => 'Early Exit'],
            3 => ['weight' => 50,  'label' => 'Half day'],
            9 => ['weight' => 75,  'label' => 'Quarter'],
        ];

        $records = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Random status choose
            $statusId = array_rand($statuses);
            $status   = $statuses[$statusId];
            $weight   = $status['weight'];

            // Salary calculation
            $calculated = round($dailySalary * ($weight / 100), 2);
            if($statusId == 5) {
                $deducted =$calculated;
            } else {
                $deducted   = round($dailySalary - $calculated, 2);

            }

            // Generate dummy check-in/out based on status
            switch ($statusId) {
                case 1: // Present
                    $checkIn  = '09:05:00';
                    $checkOut = '18:00:00';
                    break;

                case 2: // Absent
                    $checkIn  = null;
                    $checkOut = null;
                    break;

                case 3: // Late
                    $checkIn  = '09:45:00';
                    $checkOut = '18:05:00';
                    break;

                case 4: // Early Exit
                    $checkIn  = '09:00:00';
                    $checkOut = '15:00:00';
                    break;

                case 10: // Early Halfday
                    $checkIn  = '09:00:00';
                    $checkOut = '13:00:00';
                    break;

                case 11: // Early Quarter
                    $checkIn  = '09:00:00';
                    $checkOut = '16:00:00';
                    break;

                default:
                    $checkIn  = '09:10:00';
                    $checkOut = '18:00:00';
            }

            $records[] = [
                'employee_id'          => $employeeId,
                'attendance_date'      => $date->format('Y-m-d'),
                'check_in'             => $checkIn,
                'check_out'            => $checkOut,
                'attendance_status_id' => $statusId,
                'entry_weight'         => $weight,
                'calculated_salary'    => $calculated,
                'deducted_salary'      => $deducted,
                'created_at'           => now(),
                'updated_at'           => now(),
            ];
        }

        DB::table('employee_attendances')->insert($records);
    }
}
