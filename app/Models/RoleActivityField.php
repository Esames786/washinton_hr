<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleActivityField extends Model
{
    use HasFactory;

    protected $table = 'hr_role_activity_fields';

    protected $fillable = ['role_id', 'activity_field_id', 'created_by', 'updated_by'];

}
