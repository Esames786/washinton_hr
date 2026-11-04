<?php

namespace Tests\Feature;

use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Florida HR (client, 4 Nov 2026): admins mark a subcontractor's attendance by hand. It goes through
 * the shift rules like a real check-in/out and is tagged "Manual". Runs against the local test copy,
 * which carries no HR reference data, so the shift, rules, statuses and admin are made here.
 */
class ManualAttendanceTest extends TestCase
{
    private array $employees = [];
    private array $shifts = [];
    private array $statuses = [];
    private ?int $adminId = null;

    protected function setUp(): void
    {
        parent::setUp();
        foreach ([1 => 'Present', 2 => 'Late'] as $id => $name) {
            if (! DB::table('hr_attendance_statuses')->where('id', $id)->exists()) {
                DB::table('hr_attendance_statuses')->insert(['id' => $id, 'name' => $name, 'status' => 1]);
                $this->statuses[] = $id;
            }
        }
        $this->adminId = DB::table('hr_admins')->insertGetId(['name' => 'Test Admin', 'email' => 'att-admin-' . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'status' => 1, 'role_id' => 1]);
    }

    protected function tearDown(): void
    {
        DB::table('hr_employee_attendances')->whereIn('employee_id', $this->employees)->delete();
        DB::table('hr_employees')->whereIn('id', $this->employees)->delete();
        DB::table('hr_shift_attendance_rules')->whereIn('shift_type_id', $this->shifts)->delete();
        DB::table('hr_shift_types')->whereIn('id', $this->shifts)->delete();
        DB::table('hr_attendance_statuses')->whereIn('id', $this->statuses)->delete();
        DB::table('hr_admins')->where('id', $this->adminId)->delete();
        parent::tearDown();
    }

    /** A subcontractor on a shift whose Present rule starts at $start and Late 15 minutes after. */
    private function employeeOnShift(string $start, string $end): int
    {
        $shift = DB::table('hr_shift_types')->insertGetId(['name' => "Test $start-$end", 'shift_start' => $start, 'shift_end' => $end, 'status' => 1]);
        $this->shifts[] = $shift;
        DB::table('hr_shift_attendance_rules')->insert([
            ['shift_type_id' => $shift, 'attendance_status_id' => 1, 'entry_time' => $start, 'entry_weight' => 0, 'status' => 1],
            ['shift_type_id' => $shift, 'attendance_status_id' => 2, 'entry_time' => date('H:i:s', strtotime($start) + 900), 'entry_weight' => 10, 'status' => 1],
        ]);
        $id = DB::table('hr_employees')->insertGetId(['full_name' => 'Attendance Test', 'email' => 'att-' . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'employee_code' => 'AT' . random_int(100000, 999999), 'employment_type_id' => 3,
            'employee_status_id' => 1, 'shift_id' => $shift, 'basic_salary' => 30000]);
        $this->employees[] = $id;

        return $id;
    }

    private function mark(array $fields)
    {
        return $this->actingAs(Admin::findOrFail($this->adminId), 'admin')
            ->postJson(route('admin.employees.attendance_manual'), $fields);
    }

    public function test_admin_marks_a_day_and_it_follows_the_shift_rules(): void
    {
        $emp = $this->employeeOnShift('09:00:00', '18:00:00');
        $day = Carbon::yesterday()->toDateString();

        $this->mark(['employee_id' => $emp, 'attendance_date' => $day, 'check_in' => '09:20', 'check_out' => '18:00', 'remarks' => 'forgot to check in'])
            ->assertOk()->assertJson(['status' => true]);

        $row = DB::table('hr_employee_attendances')->where('employee_id', $emp)->where('attendance_date', $day)->sole();
        $this->assertSame(['09:20:00', '18:00:00', 2, 2], [$row->check_in, $row->check_out, (int) $row->attendance_status_id, (int) $row->user_type]);
        $this->assertSame('Marked manually by Test Admin: forgot to check in', $row->remarks);
        $this->assertEquals(8 * 3600 + 40 * 60, (int) $row->working_hours);

        // Marking the same day again corrects it rather than adding a second row.
        $this->mark(['employee_id' => $emp, 'attendance_date' => $day, 'check_in' => '09:00', 'check_out' => '18:00'])->assertOk();
        $row = DB::table('hr_employee_attendances')->where('employee_id', $emp)->where('attendance_date', $day)->sole();
        $this->assertSame([1, '09:00:00'], [(int) $row->attendance_status_id, $row->check_in]);

        // The attendance list shows it as manual.
        $list = $this->actingAs(Admin::findOrFail($this->adminId), 'admin')->getJson(route('admin.employees.attendance_list',
            ['draw' => 1, 'start' => 0, 'length' => 10, 'employee_ids' => [$emp], 'from_date' => $day, 'to_date' => $day]),
            ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->json('data');
        $this->assertStringContainsString('Manual', $list[0]['attendance_status_name']);
    }

    public function test_a_night_shift_day_stays_on_the_chosen_date(): void
    {
        $emp = $this->employeeOnShift('20:00:00', '05:00:00');
        $day = Carbon::today()->subDays(2)->toDateString();
        $before = Carbon::parse($day)->subDay()->toDateString();
        // The previous night is still open (never checked out).
        DB::table('hr_employee_attendances')->insert(['employee_id' => $emp, 'attendance_date' => $before, 'check_in' => '20:00:00', 'attendance_status_id' => 1]);

        $this->mark(['employee_id' => $emp, 'attendance_date' => $day, 'check_in' => '20:05', 'check_out' => '05:00'])->assertOk();

        $this->assertNull(DB::table('hr_employee_attendances')->where('employee_id', $emp)->where('attendance_date', $before)->value('check_out'));
        $row = DB::table('hr_employee_attendances')->where('employee_id', $emp)->where('attendance_date', $day)->sole();
        $this->assertSame(['20:05:00', '05:00:00'], [$row->check_in, $row->check_out]);
    }

    public function test_times_outside_the_shift_and_future_days_are_refused(): void
    {
        $emp = $this->employeeOnShift('09:00:00', '18:00:00');

        $this->mark(['employee_id' => $emp, 'attendance_date' => Carbon::yesterday()->toDateString(), 'check_in' => '22:00'])
            ->assertStatus(422)->assertJson(['status' => false, 'message' => 'Check-in time is outside your shift hours']);
        $this->mark(['employee_id' => $emp, 'attendance_date' => Carbon::tomorrow()->toDateString(), 'check_in' => '09:00'])
            ->assertStatus(422)->assertJsonValidationErrors('attendance_date');
        $this->assertSame(0, DB::table('hr_employee_attendances')->where('employee_id', $emp)->count());
    }
}
