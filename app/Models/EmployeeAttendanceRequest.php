<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendanceRequest extends Model
{
    use HasFactory;

    protected $table = 'hr_employee_attendance_requests';

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function ticket()
    {
        return $this->belongsTo(EmployeeTicket::class, 'ticket_id');
    }
}
