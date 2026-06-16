<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionSlab extends Model
{
    protected $table = 'hr_commission_slabs';

    protected $fillable = ['commission_setting_id', 'profit_from', 'profit_to', 'value'];

    protected $casts = [
        'profit_from' => 'float',
        'profit_to'   => 'float',
        'value'       => 'float',
    ];

    public function commission_setting()
    {
        return $this->belongsTo(CommissionSetting::class, 'commission_setting_id');
    }
}
