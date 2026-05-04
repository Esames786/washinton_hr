<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeHolidayException extends Model
{
    use HasFactory;

    protected $table = 'hr_employee_holiday_exceptions';

    protected $fillable = [
        'employee_id',
        'holiday_id',
        'status',
        'created_by',
        'updated_by',
    ];
}
