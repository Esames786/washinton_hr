<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TicketMessage extends Model
{
    use HasFactory;

    protected $table = 'hr_ticket_messages';

    protected $fillable = ['ticket_id', 'sender_type', 'sender_id', 'message', 'attachment_path'];


    public function ticket()
    {
        return $this->belongsTo(EmployeeTicket::class, 'ticket_id');
    }

}
