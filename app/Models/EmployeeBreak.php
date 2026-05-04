<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeBreak extends Model
{
    use HasFactory;

    protected $table = 'hr_employee_breaks';

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
