<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    use HasFactory;

    protected $table = 'hr_ticket_statuses';

//    protected $fillable = ['name', 'description'];

    public function tickets()
    {
        return $this->hasMany(EmployeeTicket::class, 'status_id');
    }
}
