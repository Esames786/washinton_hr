<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeStatus;
use Illuminate\Support\Facades\Auth;

/**
 * The holding page an agent sees while their screens are locked.
 *
 * The client asked for the portal to be locked during interview / mock test / offer, and
 * again at management approval. Rather than just refusing access, this explains where they
 * are in the process and what happens next — so they are not left wondering whether
 * something is broken.
 */
class LockedScreenController extends Controller
{
    public function __invoke()
    {
        $employee = Auth::guard('employee')->user();
        $status   = EmployeeStatus::nameFor($employee->employee_status_id);

        [$heading, $message] = match ($status) {
            EmployeeStatus::IN_PROGRESS => [
                'Your application is in progress',
                'Thank you for completing your questionnaire. Our recruitment team is reviewing it '
                . 'and will be in touch to arrange your interview and a short practical assessment. '
                . 'Your portal will open again once that stage is complete.',
            ],
            EmployeeStatus::MANAGEMENT_APPROVAL => [
                'Awaiting management approval',
                'Your documents have been received and your account is with our management team for '
                . 'final approval. This is the last check before your contract is issued.',
            ],
            default => [
                'Your account is on hold',
                'Your account is currently being reviewed by our team. We will email you as soon as '
                . 'there is an update.',
            ],
        };

        // The journey, with everything before the current stage marked done.
        $journey = [
            EmployeeStatus::NDA_PENDING           => 'NDA signed',
            EmployeeStatus::QUESTIONNAIRE_PENDING => 'Questionnaire completed',
            EmployeeStatus::IN_PROGRESS           => 'Interview & practical assessment',
            EmployeeStatus::DOCUMENTS_PENDING     => 'Documents submitted',
            EmployeeStatus::DOCS_VERIFICATION     => 'Documents verified',
            EmployeeStatus::MANAGEMENT_APPROVAL   => 'Management approval',
            EmployeeStatus::CONTRACT_PENDING      => 'Contract signed',
            EmployeeStatus::ONBOARDING            => 'Onboarding',
            EmployeeStatus::ACTIVE                => 'Start selling',
        ];

        $here  = EmployeeStatus::pipelinePosition($status);
        $steps = [];
        $i     = 0;

        foreach ($journey as $key => $label) {
            $pos     = EmployeeStatus::pipelinePosition($key);
            $steps[] = [
                'label' => $label,
                'state' => $pos < $here ? 'done' : ($pos === $here ? 'current' : 'todo'),
            ];
            $i++;
        }

        return view('employee.locked', compact('employee', 'heading', 'message', 'steps'));
    }
}
