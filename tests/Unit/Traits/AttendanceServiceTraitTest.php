<?php

namespace Tests\Unit\Traits;

use App\Exceptions\TraitException;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\ShiftType;
use App\Models\ShiftAttendanceRule;
use App\Traits\AttendanceServiceTrait;
use Tests\TestCase;

class AttendanceServiceTraitTest extends TestCase
{
    use AttendanceServiceTrait;

    protected $employee;
    protected $shift;
    protected $rules;

    private static $AttendanceStatus = [
        'LATE'           => 1,
        'PRESENT'        => 2,
        'HALF_DAY'       => 3,
        'EARLY_EXIT'     => 4,
        'ABSENT'         => 5,
        'HOLIDAY'        => 6,
        'WEEKEND'        => 7,
        'LEAVE'          => 8,
        'QUARTER'        => 9,
        'EARLY_HALFDAY'  => 10,
        'EARLY_QUARTER'  => 11,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->employee = Employee::find(1);
        if (!$this->employee) {
            $this->fail('Employee with ID 1 not found. Ensure it exists.');
        }

        $this->shift = ShiftType::find(1);
        if (!$this->shift) {
            $this->fail('Shift with ID 1 not found. Ensure it exists.');
        }

        $this->rules = ShiftAttendanceRule::where('shift_type_id', 1)->get();
        if ($this->rules->isEmpty()) {
            $this->fail('No attendance rules found for shift 1. Ensure they exist.');
        }
    }

    /** @test */
    public function it_handles_on_time_check_in()
    {
        $attendanceDate = '2025-09-14';
        $checkInTime = '16:00:22';

        $result = $this->saveAttendance($this->employee->id, $attendanceDate, $checkInTime);

        $this->assertTrue($result['status']);
        $this->assertEquals('Checked in successfully.', $result['message']);

        $attendance = EmployeeAttendance::where('employee_id', $this->employee->id)
            ->where('attendance_date', $attendanceDate)
            ->first();

        $this->assertEquals(self::$AttendanceStatus['PRESENT'], $attendance->attendance_status_id);
    }

    /** @test */
    public function it_handles_late_check_in()
    {
        $attendanceDate = '2025-09-14';
        $checkInTime = '16:20:00';

        $this->saveAttendance($this->employee->id, $attendanceDate, $checkInTime);

        $attendance = EmployeeAttendance::where('employee_id', $this->employee->id)
            ->where('attendance_date', $attendanceDate)
            ->first();

        $this->assertEquals(self::$AttendanceStatus['LATE'], $attendance->attendance_status_id);
    }

    /** @test */
    public function it_handles_half_day_check_in()
    {
        $attendanceDate = '2025-09-14';
        $checkInTime = '17:05:00';

        $this->saveAttendance($this->employee->id, $attendanceDate, $checkInTime);

        $attendance = EmployeeAttendance::where('employee_id', $this->employee->id)
            ->where('attendance_date', $attendanceDate)
            ->first();

        $this->assertEquals(self::$AttendanceStatus['HALF_DAY'], $attendance->attendance_status_id);
    }

    /** @test */
    public function it_handles_quarter_day_check_in()
    {
        $attendanceDate = '2025-09-14';
        $checkInTime = '18:05:00';

        $this->saveAttendance($this->employee->id, $attendanceDate, $checkInTime);

        $attendance = EmployeeAttendance::where('employee_id', $this->employee->id)
            ->where('attendance_date', $attendanceDate)
            ->first();

        $this->assertEquals(self::$AttendanceStatus['QUARTER'], $attendance->attendance_status_id);
    }

    /** @test */
    public function it_handles_full_shift_check_out_no_early_exit()
    {
        $attendanceDate = '2025-09-14';
        $checkInTime = '16:00:22';
        $checkOutTime = '02:00:00';

        $this->saveAttendance($this->employee->id, $attendanceDate, $checkInTime);
        $this->saveAttendance($this->employee->id, $attendanceDate, null, $checkOutTime);

        $attendance = EmployeeAttendance::where('employee_id', $this->employee->id)
            ->where('attendance_date', $attendanceDate)
            ->first();

        $this->assertEquals(self::$AttendanceStatus['PRESENT'], $attendance->attendance_status_id);
    }

    /** @test */
//    public function it_applies_early_halfday_exit()
//    {
//        $attendanceDate = '2025-09-14';
//        $checkInTime = '16:00:22';
//        $checkOutTime = '22:15:00';
//
//        $this->saveAttendance($this->employee->id, $attendanceDate, $checkInTime);
//        $this->saveAttendance($this->employee->id, $attendanceDate, null, $checkOutTime);
//
//        $attendance = EmployeeAttendance::where('employee_id', $this->employee->id)
//            ->where('attendance_date', $attendanceDate)
//            ->first();
//        dd($attendance);
//
//        $this->assertEquals(self::$AttendanceStatus['EARLY_HALFDAY'], $attendance->attendance_status_id);
//    }

    /** @test */
    public function it_applies_early_exit_with_combined_deduction()
    {
        $attendanceDate = '2025-09-14';
        $this->saveAttendance($this->employee->id, $attendanceDate, '16:20:00');
        $this->saveAttendance($this->employee->id, $attendanceDate, null, '01:55:00');

        $attendance = EmployeeAttendance::where('employee_id', $this->employee->id)
            ->where('attendance_date', $attendanceDate)
            ->first();

        $this->assertEquals(self::$AttendanceStatus['EARLY_EXIT'], $attendance->attendance_status_id);
    }

    /** @test */
//    public function it_applies_early_quarter_exit_overnight()
//    {
//        $attendanceDate = '2025-09-14';
//        $this->saveAttendance($this->employee->id, $attendanceDate, '16:00:22');
//        $this->saveAttendance($this->employee->id, $attendanceDate, null, '01:05:00');
//
//        $attendance = EmployeeAttendance::where('employee_id', $this->employee->id)
//            ->where('attendance_date', $attendanceDate)
//            ->first();
//
//        $this->assertEquals(self::$AttendanceStatus['EARLY_QUARTER'], $attendance->attendance_status_id);
//    }

    /** @test */
//    public function it_handles_overnight_check_out_on_yesterday_record()
//    {
//        $yesterday = '2025-09-14';
//        $today = '2025-09-15';
//
//        $yesterdayAttendance = EmployeeAttendance::firstOrCreate(
//            ['employee_id' => $this->employee->id, 'attendance_date' => $yesterday],
//            ['check_in' => '16:00:22', 'attendance_status_id' => self::$AttendanceStatus['PRESENT']]
//        );
//
//        $this->saveAttendance($this->employee->id, $today, null, '01:55:00');
//
//        $updated = EmployeeAttendance::find($yesterdayAttendance->id);
//        $this->assertEquals(self::$AttendanceStatus['EARLY_EXIT'], $updated->attendance_status_id);
//    }

    /** @test */
    public function it_combines_deductions_for_early_exit_if_applicable()
    {
        $combined = $this->combineDeductPercentsIfApplicable(
            $this->employee->basic_salary,
            self::$AttendanceStatus['LATE'],
            15,
            20
        );

        $this->assertNotNull($combined);

        $noCombine = $this->combineDeductPercentsIfApplicable(
            $this->employee->basic_salary,
            self::$AttendanceStatus['ABSENT'],
            0,
            20
        );

        $this->assertNull($noCombine);
    }
}
