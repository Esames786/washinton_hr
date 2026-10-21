<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeStatus;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * The onboarding questionnaire — client workflow step 4, after the NDA.
 *
 * Submitting it is what moves the agent to "In Progress", which locks their screens while
 * the interview and practical assessment happen. That transition also fires the stage
 * email, so they are told the pause is expected.
 */
class QuestionnaireController extends Controller
{
    public function show()
    {
        $employee = Auth::guard('employee')->user();

        // The NDA comes first; without it the questionnaire is out of order.
        if ($employee->nda_required && ! $employee->nda_signed_at) {
            return redirect()
                ->route('employee.profile')
                ->with('error', 'Please sign your NDA before completing the questionnaire.');
        }

        $brand     = \App\Support\Brand::for($employee)['key'] ?? 'hellotransport';
        $questions = QuestionnaireQuestion::forBrand($brand)->get();

        $answers = QuestionnaireAnswer::where('employee_id', $employee->id)
            ->pluck('answer', 'question_id')
            ->toArray();

        return view('employee.questionnaire', compact('employee', 'questions', 'answers'));
    }

    public function store(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        $brand    = \App\Support\Brand::for($employee)['key'] ?? 'hellotransport';
        $questions = QuestionnaireQuestion::forBrand($brand)->get();

        if ($questions->isEmpty()) {
            return back()->with('error', 'There are no questions to answer yet. Please contact HR.');
        }

        // Build the rules from the questions themselves, so adding a question in the admin
        // screen is all that is needed — no code change to validate it.
        $rules    = [];
        $messages = [];
        foreach ($questions as $q) {
            $key = 'q_' . $q->id;

            $rule = $q->is_required ? ['required'] : ['nullable'];
            $rule[] = match ($q->field_type) {
                'number' => 'numeric',
                'date'   => 'date',
                default  => 'string',
            };
            if (in_array($q->field_type, ['select', 'radio'], true) && $q->choices()) {
                $rule[] = 'in:' . implode(',', $q->choices());
            }
            if ($q->field_type === 'yes_no') {
                $rule[] = 'in:Yes,No';
            }

            $rules[$key]                 = $rule;
            $messages[$key . '.required'] = 'This question needs an answer.';
            $messages[$key . '.in']       = 'Please choose one of the options.';
        }

        $validated = $request->validate($rules, $messages);

        foreach ($questions as $q) {
            QuestionnaireAnswer::updateOrCreate(
                ['employee_id' => $employee->id, 'question_id' => $q->id],
                ['answer' => $validated['q_' . $q->id] ?? null]
            );
        }

        $employee->questionnaire_submitted_at = now();

        // Submitting moves them on, which locks the screens. Only advance from the
        // questionnaire stage — an admin who has already moved them further is not undone.
        $current = EmployeeStatus::nameFor($employee->employee_status_id);
        $moved = false;
        if (in_array($current, [EmployeeStatus::QUESTIONNAIRE_PENDING, EmployeeStatus::NDA_PENDING], true)) {
            $next = EmployeeStatus::idFor(EmployeeStatus::IN_PROGRESS);
            if ($next) {
                $employee->employee_status_id = $next;
                $moved = true;
            }
        }

        $employee->save();

        // Recorded like an admin change, so the profile's status history shows the automatic move
        // (an admin looking at an older page otherwise can't tell why the status changed).
        if ($moved) {
            $history = new \App\Models\EmployeeStatusHistory();
            $history->employee_id = $employee->id;
            $history->employee_status_id = $employee->employee_status_id;
            $history->is_email_disabled = 0;
            $history->is_phone_disabled = 0;
            $history->save();
        }

        Log::info('Questionnaire submitted', [
            'employee_id' => $employee->id,
            'answers'     => $questions->count(),
            'status'      => EmployeeStatus::nameFor($employee->employee_status_id),
        ]);

        return redirect()->route('employee.locked');
    }
}
