<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Employee\SetPasswordController;
use App\Mail\ApplicantRejectedEmail;
use App\Mail\ApplicantShortlistedEmail;
use App\Models\CampaignLead;
use App\Models\Employee;
use App\Models\EmployeeStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Campaign Leads — job applications before they become people.
 *
 * Phase D of the recruitment pipeline. An application arrives from the public site and
 * sits here as a lead. Shortlisting creates the HR profile; the CRM account comes much
 * later, when onboarding completes (see EmployeeObserver).
 *
 *   Application Received -> Under Review -> Shortlisted -> (HR profile created)
 *                                        -> Rejected
 */
class AdminCampaignLeadController extends Controller
{
    /** Where a shortlisted lead starts its onboarding. */
    private const START_STATUS = EmployeeStatus::NDA_PENDING;

    public function index(Request $request)
    {
        $status = $request->query('status');

        $leads = CampaignLead::forPortal()
            ->when($status && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->query('q'), function ($q) use ($request) {
                $term = '%' . $request->query('q') . '%';
                $q->where(function ($w) use ($term) {
                    $w->where('full_name', 'like', $term)
                      ->orWhere('email', 'like', $term)
                      ->orWhere('phone', 'like', $term);
                });
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        // Counts per tab, so the admin can see where the work is without clicking through.
        $counts = CampaignLead::forPortal()
            ->selectRaw('status, COUNT(*) n')
            ->groupBy('status')
            ->pluck('n', 'status')
            ->toArray();

        $counts['all'] = array_sum($counts);

        return view('admin.campaign_leads.index', compact('leads', 'counts', 'status'));
    }

    public function show($id)
    {
        $lead = CampaignLead::forPortal()->findOrFail($id);

        return view('admin.campaign_leads.show', compact('lead'));
    }

    /** Move a lead to Under Review. */
    public function review($id)
    {
        $lead = CampaignLead::forPortal()->findOrFail($id);

        if ($lead->is_shortlisted) {
            return back()->with('error', 'This lead has already been shortlisted.');
        }

        $lead->status = CampaignLead::UNDER_REVIEW;
        $lead->save();

        return back()->with('success', 'Marked as under review.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['rejection_note' => 'nullable|string|max:1000']);

        $lead = CampaignLead::forPortal()->findOrFail($id);

        if ($lead->is_shortlisted) {
            return back()->with('error', 'This lead has already been shortlisted — it cannot be rejected.');
        }

        $lead->status         = CampaignLead::REJECTED;
        $lead->rejection_note = $request->input('rejection_note');
        $lead->save();

        // Email trigger 3 — the applicant is told, with the reason if one was given.
        // A mail failure must never undo the rejection.
        $emailed = $this->safeMail($lead->email, new ApplicantRejectedEmail(
            $lead->full_name,
            $lead->rejection_note,
            $this->brandName()
        ), 'rejection', $lead->id);

        return back()->with('success', $emailed
            ? 'Lead rejected and the applicant has been notified.'
            : 'Lead rejected, but the notification email could not be sent — check the mail log.');
    }

    /**
     * Shortlist — this is where a lead becomes a person.
     *
     * Creates the HR profile carrying `cr_application_id`, which is what tells
     * EmployeeObserver to hold the CRM account back until onboarding finishes.
     */
    public function shortlist($id)
    {
        $lead = CampaignLead::forPortal()->findOrFail($id);

        if ($lead->is_shortlisted) {
            return back()->with('error', 'This lead has already been shortlisted.');
        }

        // An employee may already exist from an earlier signup under the same address.
        $existing = Employee::where('email', $lead->email)->first();
        if ($existing) {
            $lead->status = CampaignLead::SHORTLISTED;
            $lead->save();

            return back()->with('error',
                'A subcontractor with this email already exists (#' . $existing->id . '). '
                . 'The lead was marked shortlisted but no new profile was created.');
        }

        $startStatusId = EmployeeStatus::idFor(self::START_STATUS);
        if (! $startStatusId) {
            return back()->with('error',
                'The "' . self::START_STATUS . '" status is missing. Run the onboarding status migration first.');
        }

        try {
            $employee = new Employee();

            $employee->full_name          = $lead->full_name;
            $employee->email              = $lead->email;
            // The applicant chose this password when applying; it is already hashed.
            $employee->password           = $lead->password ?: bcrypt(str()->random(16));
            $employee->employee_code       = $this->generateEmployeeCode();
            $employee->joining_date        = now()->toDateString();
            $employee->employment_type_id  = 3;             // Probation
            $employee->worker_type         = 'subcontractor';
            $employee->employee_status_id  = $startStatusId; // NDA Pending — start of onboarding
            $employee->phone               = $lead->phone;
            $employee->address             = $lead->address;
            $employee->country             = $lead->country;
            $employee->city                = $lead->city;
            $employee->state               = $lead->state;
            $employee->cnic                = $lead->national_id;
            $employee->dob                 = $lead->dob;
            $employee->gender              = $lead->gender;
            $employee->marital_status      = $lead->marital_status;
            $employee->father_name         = $lead->father_name;

            // Defaults mirroring HrBridgeController::createEmployee for an Order Taker.
            $employee->role_id             = 2;
            $employee->department_id       = 1;
            $employee->designation_id      = 1;
            $employee->shift_id            = 1;
            $employee->account_type_id     = 3;
            $employee->commission_id       = 1;
            $employee->gratuity_id         = null;          // subcontractors get none
            $employee->basic_salary        = 0;             // paid commission, not salary
            $employee->is_taxable          = 0;
            $employee->tax_slab_setting_id = 1;

            // NDA is the first thing they are asked for once they log in.
            $employee->nda_required        = 1;

            // THE marker: this employee came through the pipeline, so EmployeeObserver
            // defers their CRM account until they reach Onboarding.
            $employee->cr_application_id   = $lead->id;

            $employee->save();

            $lead->status = CampaignLead::SHORTLISTED;
            $lead->save();

            Log::info('CampaignLead shortlisted', [
                'lead_id'     => $lead->id,
                'employee_id' => $employee->id,
                'email'       => $lead->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('CampaignLead shortlist failed', [
                'lead_id' => $lead->id,
                'error'   => $e->getMessage(),
            ]);

            return back()->with('error', 'Could not create the HR profile: ' . $e->getMessage());
        }

        // Email trigger 2 — the HR portal link plus a one-time link to set their own
        // password. No password is generated or emailed.
        $token   = SetPasswordController::issueToken($employee);
        $emailed = $this->safeMail($lead->email, new ApplicantShortlistedEmail(
            $lead->full_name,
            route('employee.login'),
            route('employee.set_password', $token),
            SetPasswordController::TOKEN_DAYS,
            $this->brandName()
        ), 'shortlist', $lead->id);

        return redirect()
            ->route('admin.employees.show', $employee->id)
            ->with('success', $emailed
                ? 'Shortlisted. HR profile created and the applicant has been emailed a link to set their password. '
                  . 'No CRM account yet — that is created once onboarding completes.'
                : 'Shortlisted and HR profile created, but the email could not be sent. '
                  . 'Use "Resend set-password link" on their profile, or check the mail log.');
    }

    /** Brand for the email templates — CrazyRays HR sends as CrazyRays. */
    private function brandName(): string
    {
        return config('brands.force') === 'crazyrays'
            ? 'CrazyRays Solutions'
            : 'Hello Transport';
    }

    /**
     * Send an email without ever letting a mail failure undo the decision that triggered it.
     *
     * Rejecting or shortlisting a lead is a database change the admin has already made; if
     * SMTP is down, the right outcome is "it happened but we could not tell them", not an
     * exception that leaves the admin unsure which it was.
     */
    private function safeMail(string $to, $mailable, string $kind, int $leadId): bool
    {
        try {
            Mail::to($to)->send($mailable);

            return true;
        } catch (\Throwable $e) {
            Log::error('CampaignLead: ' . $kind . ' email failed', [
                'lead_id' => $leadId,
                'to'      => $to,
                'error'   => $e->getMessage(),
            ]);

            return false;
        }
    }

    /** Same scheme AdminEmployeeController uses, so codes stay consistent. */
    private function generateEmployeeCode(): string
    {
        do {
            $code = 'HT-' . now()->format('ymd') . '-' . random_int(1000, 9999);
        } while (Employee::where('employee_code', $code)->exists());

        return $code;
    }
}
