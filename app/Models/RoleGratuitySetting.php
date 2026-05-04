<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleGratuitySetting extends Model
{
    use HasFactory;

    protected $table = 'hr_role_gratuity_settings';

    protected $fillable = ['role_id', 'gratuity_setting_id', 'created_by', 'updated_by'];

}
