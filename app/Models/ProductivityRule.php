<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityRule extends Model
{
    protected $table = 'productivity_rules';

    protected $fillable = [
        'label', 'min_percent', 'max_percent',
        'attendance_status_id', 'deduction_percent', 'status',
    ];

    protected $casts = [
        'min_percent'       => 'float',
        'max_percent'       => 'float',
        'deduction_percent' => 'float',
        'status'            => 'integer',
    ];

    /**
     * Resolve the matching band for a productive percentage (0-100).
     * Returns the active rule with the highest min_percent that is <= the value.
     */
    public static function resolveFor(float $productivePercent): ?self
    {
        return static::where('status', 1)
            ->where('min_percent', '<=', $productivePercent)
            ->orderByDesc('min_percent')
            ->first();
    }
}
