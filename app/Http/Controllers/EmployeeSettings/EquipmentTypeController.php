<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\EquipmentType;
use Illuminate\Http\Request;

class EquipmentTypeController extends Controller
{
    public function index()
    {
        return view('admin.employee_settings.equipment_types');
    }

    public function list()
    {
        $types = EquipmentType::orderBy('name')->get(['id', 'name', 'icon', 'description', 'is_active']);
        return response()->json(['data' => $types]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:equipment_types,name',
            'icon'        => 'nullable|string|max:10',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);

        $type = EquipmentType::create($data);
        return response()->json(['success' => true, 'type' => $type]);
    }

    public function update(Request $request, EquipmentType $equipment_type)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:equipment_types,name,' . $equipment_type->id,
            'icon'        => 'nullable|string|max:10',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);

        $equipment_type->update($data);
        return response()->json(['success' => true, 'type' => $equipment_type]);
    }

    public function destroy(EquipmentType $equipment_type)
    {
        if ($equipment_type->assignments()->exists()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete — this equipment type is assigned to employees.'], 422);
        }
        $equipment_type->delete();
        return response()->json(['success' => true]);
    }
}
