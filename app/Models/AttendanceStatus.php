<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceStatus extends Model
{
    use HasFactory;

    protected $table = 'hr_attendance_statuses';

    public const ABSENT       = 'Absent';
    public const UNPAID_LEAVE = 'Unpaid Leave';
    public const NCNS         = 'NCNS';   // No Call No Show

    /**
     * Whole-day statuses an admin can mark by hand without a check-in (florida, 19 Nov 2026).
     * Form value => status name. Unpaid Leave and NCNS were added after the original eleven,
     * so their ids differ per database: always look them up by name.
     */
    public const MANUAL_DAY_STATUSES = [
        'absent'       => self::ABSENT,
        'unpaid_leave' => self::UNPAID_LEAVE,
        'ncns'         => self::NCNS,
    ];

    /** Status id for a name, or null when this database doesn't have that status yet. */
    public static function idFor(string $name): ?int
    {
        $id = static::where('name', $name)->value('id');

        return $id ? (int) $id : null;
    }
}
