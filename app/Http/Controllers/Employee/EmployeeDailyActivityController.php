<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\DailyActivityField;
use App\Models\EmployeeDailyActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class EmployeeDailyActivityController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $employee_id = auth('employee')->id();
            $data = EmployeeDailyActivity::select(
                'id',
                'employee_id',
                'activity_date',
                'field_name',
                'field_value',
                'field_type'
            )
                ->where('employee_id',$employee_id )
                ->latest()
                ->get();


            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex justify-content-center gap-2">
                            <button type="button"
                                class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn"
                                data-id="'.$row->id.'">
                                <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                            </button>
                        </div>';
                })
                ->editColumn('field_value', function ($row) {
                    if ($row->field_type === 'file') {
                        return $row->field_value
                            ? '<a href="'.asset($row->field_value).'" target="_blank">View File</a>'
                            : '';
                    }
                    return e($row->field_value);
                })
                ->editColumn('activity_date', fn($row) => $row->activity_date ? date('Y-m-d', strtotime($row->activity_date)) : '')
                ->filter(function ($query) {
                    if (request()->has('search') && request('search')['value'] != '') {
                        $search = request('search')['value'];
                        $query->where('field_name', 'like', "%{$search}%")
                            ->orWhere('field_value', 'like', "%{$search}%");
                    }
                })
                ->rawColumns(['field_value', 'action'])
                ->make(true);
        }
        return view('employee.daily-activities');
    }

    public function create()
    {
        $employee = auth('employee')->user();

        $fields = DailyActivityField::whereIn('id', function($q) use ($employee) {
            $q->select('activity_field_id')
                ->from('role_activity_fields')
                ->where('role_id', $employee->role_id);
        })->get();

        $response = $fields->map(function($field) {
            return [
                'id' => $field->id,
                'label' => $field->name,
                'type' => $field->field_type,
                'required' => $field->is_required,
                'value' => ''
            ];
        });

        return response()->json($response);
    }

    public function store(Request $request)
    {
        $employee = auth('employee')->user();

        $fields = DailyActivityField::whereIn('id', function($q) use ($employee) {
            $q->select('activity_field_id')
                ->from('role_activity_fields')
                ->where('role_id', $employee->role_id);
        })->get();

        $rules = [];
        $attributes=[];
        foreach ($fields as $field) {
            $rules['field_'.$field->id] = $field->is_required ? 'required' : 'nullable';
            $attributes['field_'.$field->id] = $field->name;
            if ($field->field_type === 'file') {
                $rules['field_'.$field->id] .= '|file';
            }
        }

        $validated = $request->validate($rules,[],$attributes);

        foreach ($fields as $field) {
            $value = null;

            if ($field->field_type === 'file' && $request->hasFile('field_'.$field->id)) {

                $file = $request->file('field_'.$field->id);

                // Custom directory & filename
                $directory = public_path('Uploads/employee_activity/' . $employee->id);
//                if (!file_exists($directory)) {
//                    mkdir($directory, 0755, true);
//                }

                $filename = 'activity_' . $field->id . '_' . time() . '.' . $file->extension();
                $file->move($directory, $filename);
                $value = 'Uploads/employee_activity/' . $employee->id . '/' . $filename;
            } else {
                $value = $request->input('field_'.$field->id);
            }

            $activity = new EmployeeDailyActivity();
            $activity->employee_id = $employee->id;
            $activity->activity_date = now()->toDateString();
            $activity->field_name = $field->name;
            $activity->activity_field_id = $field->id;
            $activity->field_value = $value;
            $activity->field_type = $field->field_type;
            $activity->created_by = $employee->id;
            $activity->save();
        }

        return redirect()->route('employee.activities.index')->with('success', 'Activity saved successfully.');
    }

    public function edit($id)
    {
        $employee = auth('employee')->user();

        // Single activity record by id
        $activity = EmployeeDailyActivity::where('employee_id', $employee->id)
            ->where('id', $id)
            ->first(); // get() => collection, first() => single object

        if (!$activity) {
            return response()->json(['message' => 'Activity not found.'], 404);
        }

        // Get the related field info
        $field = DailyActivityField::find($activity->activity_field_id);

        if (!$field) {
            return response()->json(['message' => 'Field not found.'], 404);
        }

        $response = [
            [
                'id' => $field->id,
                'label' => $field->name,
                'type' => $field->field_type,
                'required' => $field->is_required,
                'value' => $activity->field_value
            ]
        ];

        return response()->json([
            'id' => $id,
            'fields' => $response
        ]);
    }


//    public function edit($id)
//    {
//        $employee = auth('employee')->user();
//
//        $activities = EmployeeDailyActivity::where('employee_id', $employee->id)
//            ->where('id', $id)
//            ->get()
//            ->keyBy('activity_field_id');
//        dd($activities);
//
//        $fields = DailyActivityField::whereIn('id', function($q) use ($employee) {
//            $q->select('activity_field_id')
//                ->from('role_activity_fields')
//                ->where('role_id', $employee->role_id);
//        })->get();
//
//        $response = $fields->map(function($field) use ($activities) {
//            return [
//                'id' => $field->id,
//                'label' => $field->name,
//                'type' => $field->field_type,
//                'required' => $field->is_required,
//                'value' => isset($activities[$field->id]) ? $activities[$field->id]->field_value : ''
//            ];
//        });
//
//        return response()->json([
//            'id' => $id,
//            'fields' => $response
//        ]);
//    }

    public function update(Request $request, $id)
    {

        $employee = auth('employee')->user();

        $fields = DailyActivityField::whereIn('id', function($q) use ($employee, $id) {
            $q->select('activity_field_id')
                ->from('role_activity_fields')
                ->where('role_id', $employee->role_id)
                ->where('activity_field_id', $id);
        })->get();

        $rules = [];
        $attributes=[];
        foreach ($fields as $field) {
            $rules['field_'.$field->id] = $field->is_required ? 'required' : 'nullable';
            $attributes['field_'.$field->id] = $field->name;

            if ($field->field_type === 'file') {
                $rules['field_'.$field->id] .= '|file';
            }
        }

        $validated = $request->validate($rules,[],$attributes);

        foreach ($fields as $field) {
            $value = null;

            $activity = EmployeeDailyActivity::firstOrNew([
                'employee_id' => $employee->id,
                'activity_date' => now()->toDateString(),
                'activity_field_id' => $field->id,
            ]);

            if ($field->field_type === 'file' && $request->hasFile('field_'.$field->id)) {
                // Remove old file
                if ($activity->field_value && file_exists(public_path($activity->field_value))) {
                    unlink(public_path($activity->field_value));
                }

                $file = $request->file('field_'.$field->id);
                $directory = public_path('Uploads/employee_activity/' . $employee->id);
//                if (!file_exists($directory)) {
//                    mkdir($directory, 0755, true);
//                }

                $filename = 'activity_' . $field->id . '_' . time() . '.' . $file->extension();
                $file->move($directory, $filename);
                $value = 'Uploads/employee_activity/' . $employee->id . '/' . $filename;
            } else {
                $value = $request->input('field_'.$field->id);
            }

            $activity->field_value = $value;
            $activity->updated_by = $employee->id;

            if (!$activity->exists) {
                $activity->created_by = $employee->id;
            }

            $activity->save();
        }

        return redirect()->route('employee.activities.index')->with('success', 'Activity updated successfully.');
    }


    public function destroy($id) {}
}

