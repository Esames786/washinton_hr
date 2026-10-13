<?php

namespace App\Http\Controllers;

use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireQuestion;
use Illuminate\Http\Request;

/**
 * Where an admin writes the onboarding questionnaire.
 *
 * The client never supplied the questions, so rather than hardcode a guess they are
 * authored here — the same way document types and daily-activity fields already are.
 * The questionnaire can then change without a deployment.
 */
class AdminQuestionnaireController extends Controller
{
    public function index()
    {
        $questions = QuestionnaireQuestion::orderBy('sort_order')->orderBy('id')->get();

        // How many agents have answered each, so a question in use is not deleted blindly.
        $answerCounts = QuestionnaireAnswer::selectRaw('question_id, COUNT(*) n')
            ->groupBy('question_id')
            ->pluck('n', 'question_id')
            ->toArray();

        return view('admin.questionnaire.index', compact('questions', 'answerCounts'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        QuestionnaireQuestion::create($data);

        return back()->with('success', 'Question added.');
    }

    public function update(Request $request, $id)
    {
        $question = QuestionnaireQuestion::findOrFail($id);
        $question->update($this->validated($request));

        return back()->with('success', 'Question updated.');
    }

    public function destroy($id)
    {
        $question = QuestionnaireQuestion::findOrFail($id);

        // Never orphan an answer an agent has already given — deactivate instead.
        if ($question->answers()->exists()) {
            $question->status = 0;
            $question->save();

            return back()->with('success',
                'That question has already been answered by agents, so it was deactivated rather than '
                . 'deleted — the existing answers are kept.');
        }

        $question->delete();

        return back()->with('success', 'Question deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question'    => 'required|string|max:500',
            'help_text'   => 'nullable|string|max:500',
            'field_type'  => 'required|in:' . implode(',', array_keys(QuestionnaireQuestion::TYPES)),
            'options'     => 'nullable|string|max:2000',
            'is_required' => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
            'status'      => 'nullable|boolean',
            'brand'       => 'nullable|in:hellotransport,crazyrays',
        ]);

        // A dropdown or multiple choice without choices is unanswerable.
        if (in_array($data['field_type'], ['select', 'radio'], true) && blank($data['options'] ?? null)) {
            return throw \Illuminate\Validation\ValidationException::withMessages([
                'options' => 'Add the choices, one per line, for a dropdown or multiple-choice question.',
            ]);
        }

        $data['is_required'] = (bool) ($data['is_required'] ?? false);
        $data['status']      = (bool) ($data['status'] ?? true);
        $data['sort_order']  = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
