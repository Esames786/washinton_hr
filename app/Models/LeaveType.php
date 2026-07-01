<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $table = 'hr_leave_types';

    protected $fillable = ['name', 'description', 'is_paid', 'status', 'created_by', 'updated_by'];

    protected $casts = [
        'is_paid' => 'boolean',
        'status'  => 'integer',
    ];
}
