<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollDetail extends Model
{
    use HasFactory;

    protected $table = 'hr_payroll_details';

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
    public function payroll()
    {
        return $this->belongsTo(Payroll::class,'payroll_id');
    }
    public function payslipItems()
    {
        return $this->hasMany(PayslipItem::class,'payroll_detail_id');
    }

    public function adjustments() {
        return $this->hasMany(PayslipAdjustment::class,'payroll_detail_id');
    }
}
