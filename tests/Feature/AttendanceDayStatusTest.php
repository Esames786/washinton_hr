<?php

namespace Tests\Feature;

use App\Jobs\GeneratePayrollJob;
use App\Models\Admin;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Florida HR (client, 18-19 Nov 2026): an agent absent on 12 and 14 Nov showed Present and Quarter.
 *  - Admins can now mark a whole day Absent, Unpaid Leave or NCNS (no check-in), and payroll lists them.
 *  - The daily finaliser no longer overwrites a day an admin marked.
 *  - Active time on the HR portal is booked on the shift's date, so a night shift's after-midnight
 *    work no longer counts towards the next day (which turned a no-show day into "Quarter").
 * Runs against the local test copy; statuses, shifts, admin and employees are made here.
 */
class AttendanceDayStatusTest extends TestCase
{
    private array $employees = [];
    private array $shifts = [];
    private array $statuses = [];
    private array $agentIds = [];
    private ?int $adminId = null;
    private ?int $payrollId = null;
    private int $lastAttendanceId;

    protected function setUp(): void
    {
        parent::setUp();
        // Payroll and the daily finaliser still use the original ids for these (Absent = 5).
        foreach ([2 => 'Present', 5 => 'Absent'] as $id => $name) {
            $this->ensureStatus($name, $id);
        }
        $this->ensureStatus('Unpaid Leave');
        $this->ensureStatus('NCNS');
        $this->adminId = DB::table('hr_admins')->insertGetId(['name' => 'Test Admin', 'email' => 'day-admin-' . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'status' => 1, 'role_id' => 1]);
        $this->lastAttendanceId = (int) DB::table('hr_employee_attendances')->max('id');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        if ($this->payrollId) {
            $details = DB::table('hr_payroll_details')->where('payroll_id', $this->payrollId)->pluck('id');
            DB::table('hr_payslip_items')->whereIn('payroll_detail_id', $details)->delete();
            DB::table('hr_payroll_details')->where('payroll_id', $this->payrollId)->delete();
            DB::table('hr_payrolls')->where('id', $this->payrollId)->delete();
        }
        // Includes rows the finaliser wrote for other employees on the test copy.
        DB::table('hr_employee_attendances')->where('id', '>', $this->lastAttendanceId)->delete();
        DB::table('hr_employee_attendances')->whereIn('employee_id', $this->employees)->delete();
        DB::table('agent_active_times')->whereIn('user_id', $this->agentIds)->delete();
        DB::table('hr_employees')->whereIn('id', $this->employees)->delete();
        DB::table('hr_shift_attendance_rules')->whereIn('shift_type_id', $this->shifts)->delete();
        DB::table('hr_shift_types')->whereIn('id', $this->shifts)->delete();
        DB::table('hr_attendance_statuses')->whereIn('id', $this->statuses)->delete();
        DB::table('hr_admins')->where('id', $this->adminId)->delete();
        parent::tearDown();
    }

    private function ensureStatus(string $name, ?int $id = null): int
    {
        $existing = DB::table('hr_attendance_statuses')->where('name', $name)->value('id');
        if ($existing) {
            return (int) $existing;
        }
        $row = ['name' => $name, 'status' => 1];
        if ($id && ! DB::table('hr_attendance_statuses')->where('id', $id)->exists()) {
            $row['id'] = $id;
        }
        $new = DB::table('hr_attendance_statuses')->insertGetId($row);
        $this->statuses[] = $new;

        return $new;
    }

    private function statusId(string $name): int
    {
        return (int) DB::table('hr_attendance_statuses')->where('name', $name)->value('id');
    }

    /** A subcontractor on a shift (Present rule at the start), 30,000 basic = 1,000 a day. */
    private function employeeOnShift(string $start, string $end, array $extra = []): int
    {
        $shift = DB::table('hr_shift_types')->insertGetId(['name' => "Day test $start-$end", 'shift_start' => $start, 'shift_end' => $end, 'status' => 1]);
        $this->shifts[] = $shift;
        DB::table('hr_shift_attendance_rules')->insert(['shift_type_id' => $shift, 'attendance_status_id' => $this->statusId('Present'),
            'entry_time' => $start, 'entry_weight' => 0, 'status' => 1]);
        $id = DB::table('hr_employees')->insertGetId(array_merge(['full_name' => 'Day Status Test', 'email' => 'day-' . uniqid() . '@example.test',
            'password' => bcrypt('x'), 'employee_code' => 'DS' . random_int(100000, 999999), 'employment_type_id' => 3,
            'employee_status_id' => 1, 'shift_id' => $shift, 'basic_salary' => 30000, 'timezone' => 'Asia/Karachi'], $extra));
        $this->employees[] = $id;

        return $id;
    }

    private function mark(array $fields)
    {
        return $this->actingAs(Admin::findOrFail($this->adminId), 'admin')
            ->postJson(route('admin.employees.attendance_manual'), $fields);
    }

    private function day(int $emp, string $date): object
    {
        return DB::table('hr_employee_attendances')->where('employee_id', $emp)->where('attendance_date', $date)->sole();
    }

    public function test_admin_marks_absent_unpaid_leave_and_ncns_as_whole_days(): void
    {
        $emp = $this->employeeOnShift('20:00:00', '04:00:00');
        [$d1, $d2, $d3] = [Carbon::today()->subDays(3)->toDateString(), Carbon::today()->subDays(2)->toDateString(), Carbon::yesterday()->toDateString()];

        // The 12 Nov case: a check-in that was never worked, counted as Present.
        DB::table('hr_employee_attendances')->insert(['employee_id' => $emp, 'attendance_date' => $d1, 'check_in' => '20:08:28',
            'attendance_status_id' => $this->statusId('Present'), 'entry_weight' => 0, 'calculated_salary' => 1000, 'deducted_salary' => 0,
            'overtime_seconds' => 600, 'overtime_amount' => 27.78, 'created_by' => $emp]);

        $this->mark(['employee_id' => $emp, 'attendance_date' => $d1, 'mark_as' => 'absent', 'remarks' => 'did not work'])
            ->assertOk()->assertJson(['status' => true]);
        $row = $this->day($emp, $d1);
        $this->assertSame($this->statusId('Absent'), (int) $row->attendance_status_id);
        $this->assertNull($row->check_in);
        $this->assertSame(['1000.00', '0.00', '0.00', 2], [$row->deducted_salary, $row->calculated_salary, $row->overtime_amount, (int) $row->user_type]);
        $this->assertSame('Marked manually by Test Admin: did not work', $row->remarks);

        $this->mark(['employee_id' => $emp, 'attendance_date' => $d2, 'mark_as' => 'unpaid_leave'])->assertOk();
        $row = $this->day($emp, $d2);
        $this->assertSame([$this->statusId('Unpaid Leave'), '1000.00'], [(int) $row->attendance_status_id, $row->deducted_salary]);

        // NCNS can be made heavier per shift through a rule (Settings > Shift Rules): 150% here.
        DB::table('hr_shift_attendance_rules')->insert(['shift_type_id' => DB::table('hr_employees')->where('id', $emp)->value('shift_id'),
            'attendance_status_id' => $this->statusId('NCNS'), 'entry_time' => null, 'entry_weight' => 150, 'status' => 1]);
        $this->mark(['employee_id' => $emp, 'attendance_date' => $d3, 'mark_as' => 'ncns'])->assertOk();
        $row = $this->day($emp, $d3);
        $this->assertSame([$this->statusId('NCNS'), '1500.00', 150], [(int) $row->attendance_status_id, $row->deducted_salary, (int) $row->entry_weight]);

        // Marking a day off as a check-in again still goes through the shift rules.
        $this->mark(['employee_id' => $emp, 'attendance_date' => $d2, 'check_in' => '20:00'])->assertOk();
        $this->assertSame([$this->statusId('Present'), '20:00:00'], [(int) $this->day($emp, $d2)->attendance_status_id, $this->day($emp, $d2)->check_in]);

        // The list shows NCNS with the Manual tag, and no productive time on a day off.
        $list = $this->actingAs(Admin::findOrFail($this->adminId), 'admin')->getJson(route('admin.employees.attendance_list',
            ['draw' => 1, 'start' => 0, 'length' => 10, 'employee_ids' => [$emp], 'from_date' => $d3, 'to_date' => $d3]),
            ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->json('data');
        $this->assertStringContainsString('>NCNS</span>', $list[0]['attendance_status_name']);
        $this->assertStringContainsString('Manual', $list[0]['attendance_status_name']);
    }

    public function test_mark_attendance_offers_the_whole_day_statuses(): void
    {
        $html = $this->actingAs(Admin::findOrFail($this->adminId), 'admin')
            ->get(route('admin.employees.attendance_list'))->assertOk()->getContent();

        $this->assertStringContainsString('<select name="mark_as" id="manual_mark_as"', $html);
        foreach (['value="absent">Absent<', 'value="unpaid_leave">Unpaid Leave<', 'value="ncns">NCNS (No Call No Show)<'] as $option) {
            $this->assertStringContainsString($option, $html);
        }
    }

    public function test_bad_input_is_refused(): void
    {
        $emp = $this->employeeOnShift('09:00:00', '18:00:00');
        $day = Carbon::yesterday()->toDateString();

        $this->mark(['employee_id' => $emp, 'attendance_date' => $day])->assertStatus(422)->assertJsonValidationErrors('check_in');
        $this->mark(['employee_id' => $emp, 'attendance_date' => $day, 'mark_as' => 'holiday'])->assertStatus(422)->assertJsonValidationErrors('mark_as');

        // A database without the NCNS status yet gets a clear message, not a broken row.
        DB::table('hr_attendance_statuses')->where('name', 'NCNS')->update(['name' => 'NCNS-hidden-for-test']);
        try {
            $this->mark(['employee_id' => $emp, 'attendance_date' => $day, 'mark_as' => 'ncns'])
                ->assertStatus(422)->assertJson(['status' => false, 'message' => 'The "NCNS" attendance status has not been set up yet.']);
        } finally {
            DB::table('hr_attendance_statuses')->where('name', 'NCNS-hidden-for-test')->update(['name' => 'NCNS']);
        }
        $this->assertSame(0, DB::table('hr_employee_attendances')->where('employee_id', $emp)->count());
    }

    public function test_payroll_lists_unpaid_leave_and_ncns_days_separately(): void
    {
        $emp = $this->employeeOnShift('09:00:00', '18:00:00');
        $from = Carbon::today()->subDays(10);
        foreach (['absent' => 6, 'unpaid_leave' => 5, 'ncns' => 4] as $as => $daysAgo) {
            $this->mark(['employee_id' => $emp, 'attendance_date' => Carbon::today()->subDays($daysAgo)->toDateString(), 'mark_as' => $as])->assertOk();
        }
        $this->payrollId = DB::table('hr_payrolls')->insertGetId(['payroll_month' => $from->format('Y-m'), 'status_id' => 1,
            'from_date' => $from->toDateString(), 'to_date' => Carbon::today()->toDateString()]);

        (new GeneratePayrollJob($this->payrollId))->handle();

        $detail = DB::table('hr_payroll_details')->where('payroll_id', $this->payrollId)->where('employee_id', $emp)->sole();
        $items = DB::table('hr_payslip_items')->where('payroll_detail_id', $detail->id)->pluck('amount', 'description')->map(fn ($a) => (float) $a)->all();
        $this->assertSame(['Absent Days: 1' => -1000.0, 'Unpaid Leave Days: 1' => -1000.0, 'NCNS Days: 1' => -1000.0], $items);
        $this->assertEquals(3000, (float) $detail->total_deductions);
        $this->assertEquals(27000, (float) $detail->net_salary);
    }

    public function test_daily_finaliser_leaves_a_day_an_admin_marked_alone(): void
    {
        $marked = $this->employeeOnShift('09:00:00', '18:00:00');
        $other  = $this->employeeOnShift('09:00:00', '18:00:00');
        $day = '2026-11-14';
        Carbon::setTestNow(Carbon::parse("$day 10:00:00", 'Asia/Karachi'));
        $this->mark(['employee_id' => $marked, 'attendance_date' => $day, 'mark_as' => 'ncns'])->assertOk();

        Carbon::setTestNow(Carbon::parse("$day 20:00:00", 'Asia/Karachi'));   // after shift end + buffer
        $this->artisan('attendance:daily')->assertExitCode(0);

        $this->assertSame($this->statusId('NCNS'), (int) $this->day($marked, $day)->attendance_status_id);
        $this->assertSame(2, (int) $this->day($marked, $day)->user_type);
        // The finaliser did run: the unmarked agent with no check-in and no activity is Absent.
        $this->assertSame($this->statusId('Absent'), (int) $this->day($other, $day)->attendance_status_id);
    }

    public function test_hr_portal_active_time_after_midnight_counts_for_the_night_shift_it_belongs_to(): void
    {
        $agent = 900000 + random_int(1, 99999);
        $this->agentIds[] = $agent;
        $emp = Employee::findOrFail($this->employeeOnShift('20:00:00', '04:00:00', ['agent_id' => $agent, 'is_logged_in' => 1]));

        // 13 Nov shift (20:00 -> 04:00): 01:30 on the 14th is still the 13th's shift.
        Carbon::setTestNow(Carbon::parse('2026-11-14 01:30:00', 'Asia/Karachi'));
        $this->actingAs($emp, 'employee')->postJson(route('employee.activity.heartbeat'), ['seconds' => 60])->assertOk();
        // The 14th's own shift, that evening.
        Carbon::setTestNow(Carbon::parse('2026-11-14 21:00:00', 'Asia/Karachi'));
        $this->actingAs($emp, 'employee')->postJson(route('employee.activity.heartbeat'), ['seconds' => 30])->assertOk();

        $this->assertSame(['2026-11-13' => 60, '2026-11-14' => 30],
            DB::table('agent_active_times')->where('user_id', $agent)->orderBy('work_date')->pluck('active_seconds', 'work_date')
                ->map(fn ($s) => (int) $s)->all());
    }
}
