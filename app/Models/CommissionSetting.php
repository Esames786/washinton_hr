<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionSetting extends Model
{
    use HasFactory;

    protected $table = 'hr_commission_settings';

    protected $casts = [
        'is_slab_based' => 'boolean',
    ];

    public function commission_type()
    {
        return $this->belongsTo(CommissionType::class, 'commission_type_id');
    }

    public function target_type()
    {
        return $this->belongsTo(CommissionTargetType::class, 'target_type_id');
    }

    public function role()
    {
        return $this->belongsToMany(Role::class, 'hr_role_commission_settings', 'commission_setting_id', 'role_id');
    }

    public function slabs()
    {
        return $this->hasMany(CommissionSlab::class, 'commission_setting_id')->orderBy('profit_from');
    }
}
