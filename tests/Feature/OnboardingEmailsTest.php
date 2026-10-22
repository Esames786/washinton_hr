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
 * Onboarding e-mails and the CRM account (client, 23 Oct 2026): the automatic NDA sent no e-mail,
 * a contract sent two, and the CRM account appeared before Onboarding. Runs against the local
 * hello_devtest copy; e-mails are read from the array mailer, the CRM bridge is faked.
 */
class OnboardingEmailsTest extends TestCase
{
    private array $made = [];

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['crm.example.test/*' => Http::response(['user_id' => 990001], 200)]);
    }

    protected function tearDown(): void
    {
        DB::table('hr_employees')->whereIn('id', $this->made)->delete();
        parent::tearDown();
    }

    /** A recruitment-pipeline hire (they came through an application) at the given stage. */
    private function hire(string $status, array $extra = []): Employee
    {
        $e = new Employee();
        $e->forceFill(array_merge([
            'full_name' => 'Onboarding Test', 'email' => 'onboarding-' . uniqid() . '@example.test',
            'password' => bcrypt('secret'), 'employee_code' => 'T' . random_int(100000, 999999),
            'employment_type_id' => 3, 'employee_status_id' => EmployeeStatus::idFor($status),
            'cr_application_id' => 999999, 'nda_required' => 0, 'phone' => '5550100',
        ], $extra))->save();
        $this->made[] = $e->id;

        return $e->fresh();
    }

    private function moveTo(Employee $e, string $status): void
    {
        $e->employee_status_id = EmployeeStatus::idFor($status);
        $e->save();
    }

    /** Subjects of every e-mail sent so far (Mailables and Mail::raw alike). */
    private function subjects(): array
    {
        return app('mailer')->getSymfonyTransport()->messages()
            ->map(fn ($m) => $m->getOriginalMessage()->getSubject())->values()->all();
    }

    private function sentToCrm(): bool
    {
        return Http::recorded(fn (HttpRequest $r) => str_contains($r->url(), '/bridge/employee/sync'))->isNotEmpty();
    }

    public function test_the_automatic_nda_at_shortlist_is_emailed(): void
    {
        $this->hire(EmployeeStatus::NDA_PENDING, ['nda_required' => 1]);

        $this->assertContains('Your NDA is ready to sign', $this->subjects());
        $this->assertFalse($this->sentToCrm());
    }

    public function test_moving_a_hire_to_nda_pending_asks_for_the_nda(): void
    {
        $e = $this->hire(EmployeeStatus::QUESTIONNAIRE_PENDING);
        $this->moveTo($e, EmployeeStatus::NDA_PENDING);

        $this->assertSame(1, (int) DB::table('hr_employees')->where('id', $e->id)->value('nda_required'));
        $this->assertContains('Your NDA is ready to sign', $this->subjects());
    }

    public function test_contract_saved_first_then_status_sends_one_email(): void
    {
        $e = $this->hire(EmployeeStatus::MANAGEMENT_APPROVAL);
        app(AdminEmployeeController::class)->saveContract($e, Request::create('/', 'POST', ['contract' => 'Terms']));
        $this->moveTo($e->fresh(), EmployeeStatus::CONTRACT_PENDING);

        $this->assertSame(['Your contract is ready'], $this->contractSubjects());
    }

    public function test_status_first_then_contract_sends_one_email(): void
    {
        $e = $this->hire(EmployeeStatus::MANAGEMENT_APPROVAL);
        $this->moveTo($e, EmployeeStatus::CONTRACT_PENDING);
        $this->assertSame([], $this->contractSubjects());   // nothing to accept yet

        app(AdminEmployeeController::class)->saveContract($e->fresh(), Request::create('/', 'POST', ['contract' => 'Terms']));
        $this->assertSame(['Action Required: New Contract Assigned'], $this->contractSubjects());
    }

    public function test_the_crm_account_waits_for_onboarding(): void
    {
        $e = $this->hire(EmployeeStatus::MANAGEMENT_APPROVAL);
        $e->phone = '5550199';                       // an Edit-form save before Onboarding
        $e->save();
        $this->moveTo($e->fresh(), EmployeeStatus::CONTRACT_PENDING);
        $this->assertFalse($this->sentToCrm());

        $this->moveTo($e->fresh(), EmployeeStatus::ONBOARDING);
        $this->assertTrue($this->sentToCrm());
        $this->assertSame(990001, (int) DB::table('hr_employees')->where('id', $e->id)->value('agent_id'));
    }

    private function contractSubjects(): array
    {
        return array_values(array_filter($this->subjects(), fn ($s) => str_contains(strtolower($s), 'contract')));
    }
}
