<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GratuityPayout extends Model
{
    use HasFactory;

    protected $table = 'hr_gratuity_payouts';

    public function payout_status() {
        return $this->belongsTo(GratuityPayoutStatus::class,'status_id');
    }

    public function employee() {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
