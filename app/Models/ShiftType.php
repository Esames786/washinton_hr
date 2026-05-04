<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftType extends Model
{
    use HasFactory;

    protected $table = 'hr_shift_types';
    protected $fillable = [
        'name',
        'shift_start',
        'shift_end',
        'status'
    ];

    public function attendanceRules()
    {
        return $this->hasMany(ShiftAttendanceRule::class, 'shift_type_id');
    }
}
