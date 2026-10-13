<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One agent's answer to one questionnaire question. */
class QuestionnaireAnswer extends Model
{
    protected $table = 'hr_employee_questionnaire_answers';

    protected $fillable = ['employee_id', 'question_id', 'answer'];

    public function question()
    {
        return $this->belongsTo(QuestionnaireQuestion::class, 'question_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
