<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory,HasRoles;

    protected $table = 'hr_admins';
    protected $guard = 'admin';   // Laravel guard config ke liye
    public $guard_name = 'admin'; // Spatie ke liye (IMPORTANT)

    public function role()
    {
        return $this->belongsTo(Role::class,'role_id');
    }

}
