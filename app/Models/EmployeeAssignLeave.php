<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAssignLeave extends Model
{
    use HasFactory;

    protected $table = 'hr_employee_assign_leaves';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'assigned_quota',
        'valid_from',
        'valid_to',
        'status',
        'created_by',
        'updated_by',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
