<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    protected $table = 'hr_ticket_types';

    protected $fillable = [
        'name',
        'description',
        'form_fields',
        'status',
        'request_type_id',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'form_fields' => 'array',
    ];

    public function tickets()
    {
        return $this->hasMany(EmployeeTicket::class);
    }
}
