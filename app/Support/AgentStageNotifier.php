<?php

namespace App\Support;

use App\Mail\AgentStatusUpdateEmail;
use App\Models\Employee;
use App\Models\EmployeeStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Tells a recruitment-pipeline agent each time their stage changes.
 *
 * Covers email triggers 4, 5 and 6 from the client's list — the NDA / questionnaire /
 * contract prompts, the management-approval notice, and the one that matters most: the
 * CRM portal link, which is how an agent learns they can finally start work. Nothing sent
 * that today; the bridge created their CRM account silently.
 *
 * Only pipeline hires are notified. The 145 employees who predate this never receive
 * these emails, because an ordinary status edit on an existing agent should not mail them.
 */
class AgentStageNotifier
{
    /**
     * What to say at each stage. Stages not listed here send nothing — "In Progress"
     * deliberately, because the agent has just been told their screens are held and a
     * second message adds no information.
     */
    private static function content(string $status, Employee $employee): ?array
    {
        $portal = rtrim(config('app.url', 'https://hr.hellotransport.com'), '/') . '/subcontractor/login';
        $crm    = rtrim(env('AGENT_PORTAL_URL', 'https://hellotransport.com'), '/');

        return match ($status) {
            EmployeeStatus::NDA_PENDING => [
                'subject' => 'Your NDA is ready to sign',
                'heading' => 'Please sign your NDA',
                'body'    => 'Before we go any further we need your non-disclosure agreement signed. '
                           . 'Sign in to your HR portal, read it through and sign it there — it only takes a minute.',
                'url'     => $portal,
                'label'   => 'Sign In & Review NDA',
                'note'    => null,
            ],
            EmployeeStatus::QUESTIONNAIRE_PENDING => [
                'subject' => 'Please complete your questionnaire',
                'heading' => 'Your questionnaire is ready',
                'body'    => 'Thank you for signing your NDA. The next step is a short questionnaire so we '
                           . 'can understand your experience before your interview.',
                'url'     => $portal,
                'label'   => 'Complete Questionnaire',
                'note'    => 'Once you submit it, your portal will pause while we arrange your interview '
                           . 'and a short practical assessment. That is expected — we will email you.',
            ],
            EmployeeStatus::DOCUMENTS_PENDING => [
                'subject' => 'Please upload your documents',
                'heading' => 'Time to send us your documents',
                'body'    => 'Good news — you have passed the interview stage. The next step is uploading '
                           . 'your documents so we can verify them. Sign in to see exactly what is needed; '
                           . 'some items depend on the country you are in.',
                'url'     => $portal,
                'label'   => 'Upload Documents',
                'note'    => 'You will need a government-issued photo ID, your tax identification, bank '
                           . 'details, a selfie and photos of your working setup.',
            ],
            EmployeeStatus::DOCS_VERIFICATION => [
                'subject' => 'We have received your documents',
                'heading' => 'Your documents are being verified',
                'body'    => 'Thank you — your documents have arrived and our team is checking them now. '
                           . 'We will be in touch if anything is unclear or missing.',
                'url'     => null,
                'label'   => null,
                'note'    => 'You do not need to do anything at this stage.',
            ],
            EmployeeStatus::MANAGEMENT_APPROVAL => [
                'subject' => 'Your account is with management for approval',
                'heading' => 'Awaiting management approval',
                'body'    => 'Your documents have been verified and your account is now with our management '
                           . 'team for final approval. This is the last check before your contract is issued.',
                'url'     => null,
                'label'   => null,
                'note'    => 'Your portal is on hold until this is complete. We will email you as soon as '
                           . 'there is an update.',
            ],
            EmployeeStatus::CONTRACT_PENDING => [
                'subject' => 'Your contract is ready',
                'heading' => 'Your contract is ready to review',
                'body'    => 'You have been approved. Your contract is waiting in your HR portal — please '
                           . 'read it carefully and accept it there to complete your onboarding.',
                'url'     => $portal,
                'label'   => 'Review & Accept Contract',
                'note'    => null,
            ],
            EmployeeStatus::ACTIVE => [
                'subject' => 'Welcome aboard — your CRM account is ready',
                'heading' => 'You are all set',
                'body'    => 'Your onboarding is complete and your CRM account is live. This is where you will '
                           . 'quote, book and track your shipments. Sign in with the same email address you '
                           . 'used for your HR portal.',
                'url'     => $crm,
                'label'   => 'Open Your CRM Portal',
                'note'    => 'Keep using your HR portal for attendance, breaks, tickets and your earnings.',
            ],
            default => null,
        };
    }

    /**
     * Send the email for an agent's new stage, if that stage has one.
     *
     * Never throws: a mail failure must not roll back a status change an admin has
     * already made, or they would be left unsure whether it took effect.
     */
    public static function notify(Employee $employee, ?string $status = null): bool
    {
        $status  = $status ?? EmployeeStatus::nameFor($employee->employee_status_id);
        $content = $status ? self::content($status, $employee) : null;

        if (! $content || ! $employee->email) {
            return false;
        }

        $brand = config('brands.force') === 'crazyrays' ? 'CrazyRays Solutions' : 'Hello Transport';

        try {
            Mail::to($employee->email)->send(new AgentStatusUpdateEmail(
                $employee->full_name,
                $content['subject'],
                $content['heading'],
                $content['body'],
                $content['url'],
                $content['label'],
                $content['note'],
                $brand
            ));

            Log::info('AgentStageNotifier: stage email sent', [
                'employee_id' => $employee->id,
                'status'      => $status,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('AgentStageNotifier: stage email failed', [
                'employee_id' => $employee->id,
                'status'      => $status,
                'error'       => $e->getMessage(),
            ]);

            return false;
        }
    }
}
