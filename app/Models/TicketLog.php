<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketLog extends Model
{
    use HasFactory;

    protected $table = 'hr_ticket_logs';

    protected $fillable = ['ticket_id', 'status_id', 'changed_by', 'remark'];

    public function ticket()
    {
        return $this->belongsTo(EmployeeTicket::class);
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }
}
