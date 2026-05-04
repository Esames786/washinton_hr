<?php

namespace Database\Seeders\old;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeAttendancesSeeder extends Seeder
{
    public function run(): void
    {
        $employees = DB::table('employees')
            ->whereIn('id', [1, 2]) // 1 = Usama, 2 = John
            ->get();

        $startDate = Carbon::create(2025, 7, 18);
        $endDate   = Carbon::create(2025, 8, 18);

        // Attendance statuses with weights
        $statuses = [
            ['id' => 1, 'name' => 'Present',     'weight' => 1.0],
            ['id' => 2, 'name' => 'Late Entry',  'weight' => 0.3],
            ['id' => 4, 'name' => 'Half Day',    'weight' => 0.5],
            ['id' => 5, 'name' => 'Absent',      'weight' => 1.0],
            ['id' => 6, 'name' => 'Holiday',     'weight' => 1.0], // Sunday/Holiday
        ];

        foreach ($employees as $employee) {
            $date = $startDate->copy();

            $perDaySalary = $employee->basic_salary / 30;

            while ($date->lte($endDate)) {
                $status = null;

                // Check if Sunday = Holiday
                if ($date->isSunday()) {
                    $status = $statuses[4]; // Holiday
                } else {
                    $status = $statuses[array_rand($statuses)];
                }

                // Salary calculation
                if ($status['id'] == 5) { // Absent
                    $calculatedSalary = $perDaySalary; // full salary (as per your requirement)
                } elseif ($status['id'] == 6) { // Holiday
                    $calculatedSalary = $perDaySalary; // holiday also full pay
                } else {
                    $calculatedSalary = $perDaySalary * $status['weight'];
                }

                DB::table('employee_attendances')->insert([
                    'employee_id'           => $employee->id,
                    'attendance_date'       => $date->toDateString(),
                    'check_in'              => in_array($status['id'], [5,6]) ? null : $date->copy()->setTime(9, rand(0, 59))->toTimeString(),
                    'check_out'             => in_array($status['id'], [5,6]) ? null : $date->copy()->setTime(18, rand(0, 59))->toTimeString(),
                    'working_hours'         => in_array($status['id'], [5,6]) ? null : rand(7, 9) . ' hours',
                    'attendance_status_id'  => $status['id'],
                    'entry_weight'          => $status['weight'],
                    'calculated_salary'     => round($calculatedSalary, 2),
                    'remarks'               => $status['name'],
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);

                $date->addDay();
            }
        }
    }
}
