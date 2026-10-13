<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A job application, seen from the HR side — the "Campaign Leads" folder.
 *
 * Reads the same `cr_applications` table the agent portal writes to. Hello and CrazyRays
 * both use it, told apart by `brand`; this portal only ever shows its own brand's leads.
 *
 * Note the table is MyISAM, so writes here are NOT transactional — never rely on a
 * rollback to undo a partial change.
 */
class CampaignLead extends Model
{
    protected $table = 'cr_applications';

    public const BRAND_HELLO     = 'hello';
    public const BRAND_CRAZYRAYS = 'crazyrays';

    /** The client's four lead states. `approved` is CrazyRays' own flow, kept for compatibility. */
    public const PENDING      = 'pending';
    public const UNDER_REVIEW = 'under_review';
    public const SHORTLISTED  = 'shortlisted';
    public const REJECTED     = 'rejected';

    public const LABELS = [
        self::PENDING      => 'Application Received',
        self::UNDER_REVIEW => 'Under Review',
        self::SHORTLISTED  => 'Shortlisted',
        self::REJECTED     => 'Rejected',
        'approved'         => 'Approved',
    ];

    /** Bootstrap badge class per state, so the list reads at a glance. */
    public const BADGES = [
        self::PENDING      => 'bg-secondary',
        self::UNDER_REVIEW => 'bg-info text-dark',
        self::SHORTLISTED  => 'bg-success',
        self::REJECTED     => 'bg-danger',
        'approved'         => 'bg-success',
    ];

    protected $fillable = [
        'brand', 'full_name', 'father_name', 'national_id', 'dob', 'gender', 'marital_status',
        'email', 'phone', 'country', 'city', 'state', 'address', 'time_zone',
        'campaign', 'employment_type', 'campaign_id', 'shift_type', 'pay_type',
        'additional_info', 'campaign_experience', 'us_experience', 'us_hours',
        'resume_path', 'linkedin_url', 'documents', 'contract_accepted_at', 'password',
        'status', 'rejection_note', 'agent_id', 'ip_address',
    ];

    protected $casts = [
        'documents'            => 'array',
        'contract_accepted_at' => 'datetime',
        'dob'                  => 'date',
    ];

    /**
     * Only this deployment's brand.
     *
     * Hello and CrazyRays are on separate databases since the split, so in practice each
     * sees only its own rows anyway — but the scope keeps it correct if they ever share again.
     */
    public function scopeForPortal($q)
    {
        $brand = config('brands.force') === 'crazyrays' ? self::BRAND_CRAZYRAYS : self::BRAND_HELLO;

        return $brand === self::BRAND_CRAZYRAYS
            ? $q->where(fn ($w) => $w->where('brand', self::BRAND_CRAZYRAYS)->orWhereNull('brand'))
            : $q->where('brand', self::BRAND_HELLO);
    }

    /** Leads still to be worked — anything not yet shortlisted or rejected. */
    public function scopeOpen($q)
    {
        return $q->whereIn('status', [self::PENDING, self::UNDER_REVIEW]);
    }

    /** The employee record created when this lead was shortlisted, if any. */
    public function employee()
    {
        return $this->hasOne(Employee::class, 'cr_application_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::BADGES[$this->status] ?? 'bg-secondary';
    }

    public function getIsShortlistedAttribute(): bool
    {
        return in_array($this->status, [self::SHORTLISTED, 'approved'], true);
    }
}
