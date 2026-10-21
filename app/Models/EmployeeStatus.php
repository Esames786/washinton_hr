<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeStatus extends Model
{
    use HasFactory;

    protected $table = 'hr_employee_statuses';

    /**
     * Statuses are referenced by NAME, never by a hardcoded id.
     *
     * Hello and CrazyRays run on separate databases since the 13 Oct split, so the same
     * status can hold a different auto-increment id on each side. Anything that compares
     * against a literal id will be subtly wrong on one of them.
     */
    public const ACTIVE                = 'Active';
    public const NDA_PENDING           = 'NDA Pending';
    public const QUESTIONNAIRE_PENDING = 'Questionnaire Pending';
    public const IN_PROGRESS           = 'In Progress';
    public const DOCUMENTS_PENDING     = 'Documents Pending';
    public const DOCS_VERIFICATION     = 'Documents Verification Pending';
    public const MANAGEMENT_APPROVAL   = 'Management Approval Pending';
    public const CONTRACT_PENDING      = 'Pending Contract';
    public const ONBOARDING            = 'Onboarding';

    /**
     * The recruitment pipeline, in order. Used to decide how far an agent has progressed.
     */
    public const PIPELINE = [
        self::NDA_PENDING,
        self::QUESTIONNAIRE_PENDING,
        self::IN_PROGRESS,
        self::DOCUMENTS_PENDING,
        self::DOCS_VERIFICATION,
        self::MANAGEMENT_APPROVAL,
        self::CONTRACT_PENDING,
        self::ONBOARDING,
        self::ACTIVE,
    ];

    /**
     * Stages at which the agent uploads their own documents: Documents Pending (nothing uploaded
     * yet, the pipeline stage built for it) and Documents Verification Pending (HR checking; older
     * and admin-created agents also start here). The profile page and the upload endpoint used to
     * allow only the latter, so an agent at Documents Pending saw no upload form.
     */
    public const UPLOAD_STAGES = [self::DOCUMENTS_PENDING, self::DOCS_VERIFICATION];

    public static function allowsDocumentUpload(?int $id): bool
    {
        return in_array(static::nameFor($id), self::UPLOAD_STAGES, true);
    }

    /** Resolve a status id from its name, cached for the request. */
    public static function idFor(string $name): ?int
    {
        static $cache = [];

        if (array_key_exists($name, $cache)) {
            return $cache[$name];
        }

        return $cache[$name] = static::where('name', $name)->value('id');
    }

    /** Resolve a status name from its id. */
    public static function nameFor(?int $id): ?string
    {
        if (! $id) {
            return null;
        }

        static $cache = [];

        if (array_key_exists($id, $cache)) {
            return $cache[$id];
        }

        return $cache[$id] = static::where('id', $id)->value('name');
    }

    /**
     * How far along the pipeline a status sits. -1 for anything outside it
     * (Inactive, Terminated, Resigned, Training, Trail, Deployed).
     */
    public static function pipelinePosition(?string $name): int
    {
        if (! $name) {
            return -1;
        }

        $i = array_search($name, self::PIPELINE, true);

        return $i === false ? -1 : $i;
    }
}
