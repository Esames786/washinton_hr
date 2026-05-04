<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;


class Role extends SpatieRole
{
    use HasFactory;

    protected $table = 'hr_roles';


    public function activityFields()
    {
        return $this->belongsToMany(DailyActivityField::class, 'role_activity_fields', 'role_id', 'activity_field_id');
    }

}
