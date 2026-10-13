<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A question on the onboarding questionnaire, written by an admin in the portal.
 *
 * Kept as data rather than code so the client can change the questionnaire without a
 * deployment — the same approach already used for document types and daily-activity fields.
 */
class QuestionnaireQuestion extends Model
{
    protected $table = 'hr_questionnaire_questions';

    public const TYPES = [
        'textarea' => 'Long answer',
        'text'     => 'Short answer',
        'number'   => 'Number',
        'date'     => 'Date',
        'select'   => 'Dropdown',
        'radio'    => 'Multiple choice',
        'yes_no'   => 'Yes / No',
    ];

    protected $fillable = [
        'question', 'help_text', 'field_type', 'options',
        'is_required', 'sort_order', 'status', 'brand',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'status'      => 'boolean',
    ];

    /** Active questions for a brand, in the admin's chosen order. */
    public function scopeForBrand($q, ?string $brand)
    {
        return $q->where('status', 1)
            ->where(fn ($w) => $w->whereNull('brand')->orWhere('brand', $brand))
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** Choices for select/radio, one per line in the admin field. */
    public function choices(): array
    {
        if (! in_array($this->field_type, ['select', 'radio'], true)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $this->options))));
    }

    public function answers()
    {
        return $this->hasMany(QuestionnaireAnswer::class, 'question_id');
    }
}
