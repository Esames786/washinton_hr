<?php

namespace Tests\Feature;

use App\Http\Controllers\Employee\EmployeeBreakController;
use App\Models\Employee;
use App\Models\EmployeeStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * "Unable to apply break" (client, 24 Oct 2026): a New York agent's afternoon is already the next
 * day in Karachi, where created_at is stored, so a started break never showed as active, End
 * found nothing and every retry opened another. Runs against the local hello_devtest copy.
 */
class BreakTimezoneTest extends TestCase
{
    private array $made = [];

    protected function tearDown(): void
    {
        DB::table('hr_employee_breaks')->whereIn('employee_id', $this->made)->delete();
        DB::table('hr_employees')->whereIn('id', $this->made)->delete();
        parent::tearDown();
    }

    public function test_a_new_york_afternoon_break_starts_shows_and_ends(): void
    {
        $this->assertSame('Asia/Karachi', config('app.timezone'));
        // 16:40 in New York on the 23rd = 01:40 on the 24th in Karachi.
        $this->travelTo(Carbon::parse('2026-10-23 16:40:00', 'America/New_York'));

        $e = new Employee();
        $e->forceFill([
            'full_name' => 'Break Test', 'email' => 'break-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'employee_code' => 'B' . random_int(100000, 999999), 'employment_type_id' => 3,
            'employee_status_id' => EmployeeStatus::idFor(EmployeeStatus::ACTIVE), 'is_logged_in' => 1,
            'timezone' => 'America/New_York', 'cr_application_id' => 999999,
        ])->save();
        $this->made[] = $e->id;
        $this->actingAs($e, 'employee');

        $this->postJson(route('employee.breaks.start'), ['reason' => 'Lunch'])->assertOk();
        $this->assertSame('on_break', EmployeeBreakController::getBreakStatus()['status']);

        // A second Start no longer opens another break.
        $this->postJson(route('employee.breaks.start'), ['reason' => 'Again'])
            ->assertStatus(400)->assertJson(['error' => 'You are already on a break.']);

        $this->travel(12)->minutes();
        $this->postJson(route('employee.breaks.end'))->assertOk();

        $row = DB::table('hr_employee_breaks')->where('employee_id', $e->id)->sole();
        $this->assertSame('16:40:00', $row->break_start);
        $this->assertSame('16:52:00', $row->break_end);
        $this->assertEquals(12.0, (float) $row->break_duration);
        $this->assertSame('available', EmployeeBreakController::getBreakStatus()['status']);
    }
}
