<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\DocumentSetting;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

class EmployeeDocumentSettingController extends Controller
{
    /**
     * Display listing of employee document settings.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DocumentSetting::select([
                'id', 'title', 'is_required', 'description', 'input_type', 'status'
            ]);

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $action = '<div class="d-flex justify-content-center gap-2">
                                 <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn" data-id="'.$row->id.'">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </button>
                              </div>';
                    return $action;
                })
                ->editColumn('is_required', function ($row) {
                    return $row->is_required ? 'Yes' : 'No';
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<button class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm active_btn" data-id="'.$row->id.'">Active</button>'
                        : '<button class="bg-neutral-200 text-neutral-600 border border-neutral-400 px-24 py-4 radius-4 fw-medium text-sm inactive_btn" data-id="'.$row->id.'">Inactive</button>';
                })
                ->filter(function ($query) {
                    if (request()->has('search') && request('search')['value'] != '') {
                        $search = request('search')['value'];
                        $query->where('title', 'like', "%{$search}%");
                    }
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('admin.employee_settings.documents');
    }

    /**
     * Store a newly created document setting.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'is_required' => 'required|boolean',
            'description' => 'nullable|string',
            'input_type'  => 'required|string|max:255',
//            'status'      => 'required|integer|in:0,1',
        ]);

        DocumentSetting::create([
            'title'       => $request->title,
            'is_required' => $request->is_required,
            'description' => $request->description,
            'input_type'  => $request->input_type,
//            'status'      => $request->status,
//            'created_by'  => Auth::id(),
        ]);

        session()->flash('success', 'Document setting created successfully.');
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified setting.
     */
    public function edit(DocumentSetting $document_setting)
    {
        return response()->json($document_setting);
    }

    /**
     * Update the specified setting.
     */
    public function update(Request $request, DocumentSetting $document_setting)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'is_required' => 'required|boolean',
            'description' => 'nullable|string',
            'input_type'  => 'required|string|max:255',
//            'status'      => 'required|integer|in:0,1', // Add if you want to update status
        ]);

        $document_setting->update([
            'title'       => $request->title,
            'is_required' => $request->is_required,
            'description' => $request->description,
            'input_type'  => $request->input_type,
//            'status'      => $request->status, // Optional if you allow status change
//            'updated_by'  => Auth::id(), // Audit
        ]);

        session()->flash('success', 'Document setting updated successfully.');
        return redirect()->back();
    }


    /**
     * Remove the specified setting.
     */
    public function destroy($id)
    {
        DocumentSetting::findOrFail($id)->delete();
        return response()->json(['success' => 'Document setting deleted successfully.']);
    }
}
