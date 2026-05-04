<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $table = 'hr_employee_attendances';

    protected $fillable = ['employee_id','attendance_date','attendance_status_id'];

    public function attendance_status()
    {
        return $this->belongsTo(AttendanceStatus::class,'attendance_status_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
