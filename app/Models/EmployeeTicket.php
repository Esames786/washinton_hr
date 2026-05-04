<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTicket extends Model
{
    use HasFactory;

    protected $table = 'hr_employee_tickets';

    protected $fillable = [
        'employee_id', 'ticket_type_id', 'status_id',
        'subject', 'description', 'extra_data',
        'admin_remark', 'approved_by'
    ];

    protected $casts = ['extra_data' => 'array'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function ticket_type()
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_id');
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class, 'ticket_id');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class, 'ticket_id');
    }

    public function approvedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function rejectedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'rejected_by');

    }
    public function attendanceRequest()
    {
        return $this->hasOne(EmployeeAttendanceRequest::class, 'ticket_id');
    }

    public function leaveRequest()
    {
        return $this->hasOne(EmployeeLeave::class, 'ticket_id');
    }

    public function adminRelation()
    {
        return $this->belongsTo(Admin::class, 'sender_id');
    }

    public function employeeRelation()
    {
        return $this->belongsTo(Employee::class, 'sender_id');
    }

// Helper to get sender
    public function getSenderAttribute()
    {
        return $this->sender_type === 'admin'
            ? $this->adminRelation
            : $this->employeeRelation;
    }

    // Helper: return sender name dynamically
    public function getSenderNameAttribute()
    {
        if ($this->sender_type === 'admin') {
            return $this->admin?->name ?? 'Admin';
        }
        if ($this->sender_type === 'employee') {
            return $this->employee?->name ?? 'Employee';
        }
        return 'Unknown';
    }
}
