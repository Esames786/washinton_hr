<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Authenticatable
{
    use HasFactory,HasRoles;

    protected $table = 'hr_employees';
    protected $guard = 'employee';   // Laravel guard config ke liye
    public $guard_name = 'employee'; // Spatie ke liye (IMPORTANT)

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function dailyActivityFields() {
        return $this->role->activityFields(); // via Role model
    }

    public function bankDetail()
    {
        return $this->hasOne(EmployeeBankDetail::class, 'employee_id');
    }
    public function shift()
    {
        return $this->belongsTo(ShiftType::class, 'shift_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id', 'id');
    }

    public function employee_status()
    {
        return $this->belongsTo(EmployeeStatus::class,'employee_status_id','id');
    }

    public function employee_payslip()
    {
        return $this->belongsTo(PayrollDetail::class, 'employee_id', 'id');

    }

    public function working_days() {
        return $this->hasMany(EmployeeWorkingDay::class,'employee_id');
    }

    public function employment_type() {
        return $this->belongsTo(EmploymentType::class,'employment_type_id');
    }

    public function account_type() {
        return $this->belongsTo(EmployeeAccountType::class,'account_type_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class,'department_id','id');
    }
    public function designation(){
        return $this->belongsTo(Designation::class,'designation_id','id');
    }

    public function assignedLeaves()
    {
        return $this->hasMany(EmployeeAssignLeave::class, 'employee_id');
    }

    public function gratuity()
    {
        return $this->belongsTo(GratuitySetting::class, 'gratuity_id');
    }


    public function holiday_exceptions()
    {
        return $this->hasMany(EmployeeHolidayException::class, 'employee_id', 'id');
    }

    public function getExcludedHolidayIdsAttribute()
    {
        return $this->holiday_exceptions()->pluck('holiday_id')->toArray();
    }

    public function isActive()
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(10));
    }

    public function tax_slab()
    {
        return $this->belongsTo(TaxSlabSetting::class, 'tax_slab_setting_id');
    }

//    public function getWorkingDaysAttribute()
//    {
//        return $this->working_days()->pluck('is_working', 'day_of_week')->toArray();
//    }


}
