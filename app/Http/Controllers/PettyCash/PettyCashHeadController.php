<?php

namespace App\Http\Controllers\PettyCash;

use App\Http\Controllers\Controller;
use App\Models\PettyCashHead;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PettyCashHeadController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PettyCashHead::select(['id', 'name', 'type', 'created_at','status']);

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $action = '<div class="d-flex justify-content-center gap-2">
                               <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn" data-id="'.$row->id.'">
                                   <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                               </button>
                           </div>';
                    return $action;
                })
                ->editColumn('type', function ($row) {
                    return ucfirst($row->type);
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 'active'
                        ? '<button class="bg-success-focus text-success-600 border border-success-main
                            px-24 py-4 radius-4 fw-medium text-sm active_btn" data-id="'.$row->id.'">Active</button>'
                        : '<button class="bg-neutral-200 text-neutral-600 border border-neutral-400
                            px-24 py-4 radius-4 fw-medium text-sm inactive_btn" data-id="'.$row->id.'">Inactive</button>';
                })
                ->editColumn('created_at', function ($row) {
                    // Human readable (Carbon ka use)
                    return $row->created_at ? $row->created_at->toDateString() : '-';
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }

        return view('admin.petty_cash.heads');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|in:income,expense',
        ]);

        PettyCashHead::create([
            'name' => $request->name,
            'type' => $request->type,
            'status' => 'active',
        ]);

        return response()->json(['success' => true, 'message' => 'Head added successfully.']);
    }

    public function edit($id)
    {
        return response()->json(PettyCashHead::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
//            'type' => 'required|in:income,expense',
            'status' => 'required|in:active,inactive',
        ]);

        $head = PettyCashHead::findOrFail($id);
        $head->update($request->only('name','status'));

        return response()->json(['success' => true, 'message' => 'Head updated successfully.']);
    }

    public function destroy($id)
    {
        PettyCashHead::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Head deleted successfully.']);
    }
}
