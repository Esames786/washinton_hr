<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\TaxSlabSetting;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AdminTaxSlabController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = TaxSlabSetting::select([
                'id', 'title', 'min_income', 'max_income', 'rate', 'type', 'global_cap', 'description', 'status'
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

        return view('admin.employee_settings.tax_slabs');
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'min_income' => 'required|numeric|min:0',
            'max_income' => 'nullable|numeric|gte:min_income',
            'rate' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
            'global_cap' => 'nullable|numeric|min:0',
            'status' => 'required|in:1,0',
        ]);

        TaxSlabSetting::create(array_merge(
            $request->all(),
            ['created_by' => auth('admin')->id()]
        ));
        session()->flash('success', 'Tax slab created successfully.');
        return redirect()->back();
    }

    public function edit(TaxSlabSetting $tax_slab)
    {
        return response()->json($tax_slab);
    }

    public function update(Request $request, TaxSlabSetting $tax_slab)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'min_income' => 'required|numeric|min:0',
            'max_income' => 'nullable|numeric|gte:min_income',
            'rate' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
            'global_cap' => 'nullable|numeric|min:0',
            'status' => 'required|in:1,0',
        ]);

        $tax_slab->update(array_merge(
            $request->all(),
            ['updated_by' =>  auth('admin')->id()]
        ));
        session()->flash('success', 'Tax slab updated successfully.');
        return redirect()->back();
    }
}
