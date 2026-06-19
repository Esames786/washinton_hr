<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeEquipment;
use App\Models\EquipmentType;
use Illuminate\Http\Request;

class EmployeeEquipmentController extends Controller
{
    public function list(int $employeeId)
    {
        $items = EmployeeEquipment::with('equipmentType')
            ->where('employee_id', $employeeId)
            ->orderBy('assigned_date', 'desc')
            ->get()
            ->map(fn($e) => [
                'id'             => $e->id,
                'type_name'      => $e->equipmentType->name ?? '-',
                'type_icon'      => $e->equipmentType->icon ?? '',
                'asset_name'     => $e->asset_name,
                'serial_number'  => $e->serial_number,
                'assigned_date'  => $e->assigned_date?->format('Y-m-d'),
                'return_date'    => $e->return_date?->format('Y-m-d'),
                'notes'          => $e->notes,
                'status'         => $e->status,
            ]);

        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'        => 'required|exists:hr_employees,id',
            'equipment_type_id'  => 'required|exists:equipment_types,id',
            'asset_name'         => 'nullable|string|max:150',
            'serial_number'      => 'nullable|string|max:100',
            'assigned_date'      => 'required|date',
            'notes'              => 'nullable|string|max:500',
        ]);

        $data['status'] = 'assigned';
        $item = EmployeeEquipment::create($data);
        $item->load('equipmentType');

        return response()->json(['success' => true, 'item' => [
            'id'            => $item->id,
            'type_name'     => $item->equipmentType->name ?? '-',
            'type_icon'     => $item->equipmentType->icon ?? '',
            'asset_name'    => $item->asset_name,
            'serial_number' => $item->serial_number,
            'assigned_date' => $item->assigned_date?->format('Y-m-d'),
            'return_date'   => null,
            'notes'         => $item->notes,
            'status'        => $item->status,
        ]]);
    }

    public function markReturned(Request $request, EmployeeEquipment $equipment)
    {
        $request->validate(['return_date' => 'required|date']);
        $equipment->update(['status' => 'returned', 'return_date' => $request->return_date]);
        return response()->json(['success' => true]);
    }

    public function destroy(EmployeeEquipment $equipment)
    {
        $equipment->delete();
        return response()->json(['success' => true]);
    }
}
