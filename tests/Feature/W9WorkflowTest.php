<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminEmployeeController;
use App\Models\Employee;
use App\Models\EmployeeStatus;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * W-9 as an optional HR onboarding step (client, 23 Oct 2026): HR sends it — no CRM account
 * needed — and the agent files it in the HR portal, which forwards it to the agent portal's
 * bridge. Runs against the local hello_devtest copy; the bridge is faked.
 */
class W9WorkflowTest extends TestCase
{
    private array $made = [];

    protected function tearDown(): void
    {
        DB::table('w9_forms')->whereIn('hr_employee_id', $this->made)->delete();
        DB::table('hr_employees')->whereIn('id', $this->made)->delete();
        parent::tearDown();
    }

    /** A Hello pipeline hire at Pending Contract: in onboarding, no CRM account yet. */
    private function agent(array $extra = []): Employee
    {
        $e = new Employee();
        $e->forceFill(array_merge([
            'full_name' => 'W9 Agent', 'email' => 'w9-agent-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'employee_code' => 'W' . random_int(100000, 999999), 'employment_type_id' => 3,
            'employee_status_id' => EmployeeStatus::idFor(EmployeeStatus::CONTRACT_PENDING), 'is_logged_in' => 1,
            'cr_application_id' => 999999,
        ], $extra))->save();
        $this->made[] = $e->id;

        return $e->fresh();
    }

    private function subjects(): array
    {
        return app('mailer')->getSymfonyTransport()->messages()
            ->map(fn ($m) => $m->getOriginalMessage()->getSubject())->values()->all();
    }

    private function form(): array
    {
        return [
            'legal_name' => 'W9 Agent', 'tax_classification' => 'individual', 'address' => '1 Main St', 'city' => 'Austin',
            'state' => 'TX', 'zip' => '73301', 'tin_type' => 'ssn', 'tin' => '123-45-6789',
            'signature_data' => 'data:image/png;base64,AAAA', 'certified' => '1',
        ];
    }

    public function test_hr_can_send_the_w9_before_the_crm_account_exists(): void
    {
        $e = $this->agent();

        app(AdminEmployeeController::class)->setW9(Request::create('/', 'POST', ['require' => 1]), $e);

        $this->assertSame(1, (int) DB::table('hr_employees')->where('id', $e->id)->value('w9_required'));
        $this->assertContains('Action Required: Complete your W-9 Form', $this->subjects());
        $this->assertTrue($e->fresh()->w9Due());
    }

    public function test_the_agent_files_it_in_the_hr_portal_through_the_bridge(): void
    {
        Http::fake(['crm.example.test/bridge/w9/submit' => Http::response(['success' => true, 'message' => 'Your W-9 has been submitted.'], 200)]);
        $e = $this->agent(['w9_required' => 1]);

        $this->actingAs($e, 'employee')->postJson(route('employee.w9.submit'), $this->form())
            ->assertOk()->assertJson(['success' => true]);

        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/bridge/w9/submit')
            && $r->header('X-Bridge-Key')[0] === 'test-bridge-key'
            && (int) $r['hr_employee_id'] === $e->id
            && $r['tin'] === '123-45-6789');
    }

    public function test_the_bridge_answer_reaches_the_form(): void
    {
        Http::fake(['crm.example.test/bridge/w9/submit' => Http::sequence()
            ->push(['message' => 'The given data was invalid.', 'errors' => ['zip' => ['Enter a valid zip code.']]], 422)
            ->push('down', 500)]);
        $e = $this->agent(['w9_required' => 1]);
        $this->actingAs($e, 'employee')->postJson(route('employee.w9.submit'), $this->form())
            ->assertStatus(422)->assertJsonPath('errors.zip.0', 'Enter a valid zip code.');

        $this->actingAs($e, 'employee')->postJson(route('employee.w9.submit'), $this->form())
            ->assertStatus(503)->assertJson(['success' => false]);
    }

    public function test_the_profile_shows_the_w9_card_and_form(): void
    {
        $e = $this->agent(['w9_required' => 1]);

        $html = $this->actingAs($e, 'employee')->get(route('employee.profile'))->assertOk()
            ->assertSee('id="w9"', false)
            ->assertSee('id="w9Overlay"', false)
            ->assertSee(route('employee.w9.submit'), false)
            ->getContent();
        // The theme resets inputs to appearance:none; the certification box must opt back in.
        $this->assertMatchesRegularExpression('/id="w9Certified"[^>]*appearance:auto/', $html);

        // Elsewhere in the portal: a reminder that links to it.
        $this->actingAs($e, 'employee')->get(route('employee.dashboard'))->assertOk()->assertSee('id="w9Reminder"', false);
    }

    public function test_only_agents_hr_asked_can_file_one(): void
    {
        Http::fake();
        $e = $this->agent();   // HR has not sent it

        $this->actingAs($e, 'employee')->postJson(route('employee.w9.submit'), $this->form())->assertStatus(422);
        Http::assertNothingSent();
    }

    public function test_a_w9_on_file_shows_as_submitted(): void
    {
        $e = $this->agent(['w9_required' => 1]);
        DB::table('w9_forms')->insert([
            'user_id' => null, 'hr_employee_id' => $e->id, 'legal_name' => 'W9 Agent', 'tax_classification' => 'individual',
            'address' => '1 Main St', 'city' => 'Austin', 'state' => 'TX', 'zip' => '73301', 'tin_type' => 'ssn',
            'tin_encrypted' => 'x', 'tin_last4' => '6789', 'signature' => 'x', 'signed_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->assertFalse($e->fresh()->w9Due());
        $this->actingAs($e, 'employee')->postJson(route('employee.w9.submit'), $this->form())->assertStatus(409);
    }
}
