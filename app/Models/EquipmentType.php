<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentType extends Model
{
    protected $table    = 'equipment_types';
    protected $fillable = ['name', 'icon', 'description', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function assignments()
    {
        return $this->hasMany(EmployeeEquipment::class, 'equipment_type_id');
    }
}
