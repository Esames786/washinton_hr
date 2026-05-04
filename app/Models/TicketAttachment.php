<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    use HasFactory;

    protected $table = 'hr_employee_ticket_attachments';
    protected $fillable = ['ticket_id', 'file_path', 'mime_type'];

    public function ticket()
    {
        return $this->belongsTo(EmployeeTicket::class);
    }
}
