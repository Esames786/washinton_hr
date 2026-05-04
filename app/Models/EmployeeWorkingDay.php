<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeWorkingDay extends Model
{

    use HasFactory;

    protected $table = 'hr_employee_working_days';

    protected $fillable = [
        'employee_id',
        'day_of_week',
        'is_working',
        'created_by',
        'updated_by',
    ];

}
