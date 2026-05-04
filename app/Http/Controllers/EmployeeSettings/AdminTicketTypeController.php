<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\TicketRequestType;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class AdminTicketTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = TicketType::select('id', 'name', 'status', 'form_fields', 'created_at');
            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex justify-content-center gap-2">
                              <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn" data-id="'.$row->id.'">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                 </button>
                            </div>';
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<button class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm active_btn" data-id="'.$row->id.'">Active</button>'
                        : '<button class="bg-neutral-200 text-neutral-600 border border-neutral-400 px-24 py-4 radius-4 fw-medium text-sm inactive_btn" data-id="'.$row->id.'">Inactive</button>';
                })
                ->editColumn('form_fields', fn($row) => $row->form_fields ? json_encode($row->form_fields) : '')
                ->rawColumns(['action','status'])
                ->make(true);
        }
//        $requestTypes = TicketRequestType::all();

        return view('admin.employee_settings.ticket_types');
    }

    private function prepareFormFields($fields)
    {
        $updated = [];
        foreach ($fields as $field) {
            $updated[] = [
                'name'     => $field['name'] ?? '',
                'slug'     => Str::slug($field['name'] ?? '', '_'),
                'type'     => $field['type'] ?? 'text',
                'required' => $field['required'] ?? false,
            ];
        }
        return $updated;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
//            'request_type_id' => 'required|integer',
            'description' => 'nullable|string',
//            'form_fields' => 'nullable|string', // JSON string from form
//            'status'      => 'required|in:0,1',
        ]);

//        $formFields = $request->form_fields ? json_decode($request->form_fields, true) : [];

        TicketType::create([
            'name'        => $request->name,
//            'request_type_id' => $request->request_type_id,
            'description' => $request->description,
//            'form_fields' => $this->prepareFormFields($formFields),
            'status'      => 1,
            'created_by'  => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Ticket Type created successfully.');
    }

    public function edit(TicketType $ticket_type)
    {
        return response()->json($ticket_type);
    }

    public function update(Request $request, TicketType $ticket_type)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
//            'request_type_id' => 'required|integer',
            'description' => 'nullable|string',
//            'form_fields' => 'nullable|string',
            'status'      => 'required|in:0,1',
        ]);

//        $formFields = $request->form_fields ? json_decode($request->form_fields, true) : [];

        $ticket_type->update([
            'name'        => $request->name,
//            'request_type_id' => $request->request_type_id,
            'description' => $request->description,
//            'form_fields' => $this->prepareFormFields($formFields),
            'status'      => $request->status,
            'updated_by'  => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Ticket Type updated successfully.');
    }

    public function destroy(TicketType $ticket_type)
    {
        $ticket_type->delete();
        return response()->json(['success' => 'Ticket Type deleted successfully.']);
    }
}
