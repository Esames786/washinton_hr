<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GratuitySetting extends Model
{
    use HasFactory;
    protected $table = 'hr_gratuity_settings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title',
        'description',
        'company_contribution_percentage',
        'employee_contribution_percentage',
        'eligibility_years',
        'status',
    ];

    public function role()
    {
        return $this->belongsToMany(Role::class, 'hr_role_gratuity_settings', 'gratuity_setting_id', 'role_id');
    }
}
