<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftAttendanceRule extends Model
{
    use HasFactory;

    protected $table = 'hr_shift_attendance_rules';

    protected $fillable = [
        'shift_type_id',
        'attendance_status_id',
        'entry_time',
        'entry_weight',
        'status',
    ];

    public function shift_type()
    {
        return $this->belongsTo(ShiftType::class, 'shift_type_id');
    }

    public function attendance_status()
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id');
    }
}
