<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeEquipment extends Model
{
    protected $table    = 'employee_equipment';
    protected $fillable = [
        'employee_id', 'equipment_type_id', 'asset_name',
        'serial_number', 'assigned_date', 'return_date', 'notes', 'status',
    ];
    protected $casts = [
        'assigned_date' => 'date',
        'return_date'   => 'date',
    ];

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class, 'equipment_type_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
