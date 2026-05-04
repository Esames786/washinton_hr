<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyActivityField extends Model
{
    use HasFactory;

    protected $table = 'hr_daily_activity_fields';


    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_activity_fields', 'activity_field_id', 'role_id');
    }
}
