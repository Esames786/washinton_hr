<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * P3 (#9): a subcontractor's SELF-REPORTED working equipment, captured on the profile
 * workplace block. Distinct from App\Models\EmployeeEquipment (company-assigned assets).
 */
class EmployeeWorkEquipment extends Model
{
    protected $table = 'hr_employee_work_equipment';

    protected $fillable = ['employee_id', 'name', 'details'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
