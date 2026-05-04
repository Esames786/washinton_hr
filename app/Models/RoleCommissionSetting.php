<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleCommissionSetting extends Model
{
    use HasFactory;

    protected $table = 'hr_role_commission_settings';

    protected $fillable = ['role_id', 'commission_setting_id', 'created_by', 'updated_by'];
}
