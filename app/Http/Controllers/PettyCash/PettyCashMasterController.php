<?php

namespace App\Http\Controllers\PettyCash;

use App\Http\Controllers\Controller;
use App\Models\PettyCashMaster;
use App\Models\PettyCashMasterHistory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PettyCashMasterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PettyCashMaster::select(['id', 'title', 'opening_balance', 'current_balance', 'created_at']);

            return DataTables::of($data)
//                ->addColumn('action', function ($row) {
//                    return '
//                        <button class="btn btn-sm btn-success edit_btn" data-id="'.$row->id.'">
//                            <i class="fas fa-edit"></i>
//                        </button>';
//                })
                ->make(true);
        }
        $hasMaster = PettyCashMaster::exists();
        return view('admin.petty_cash.opening_balance',compact('hasMaster'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $master = PettyCashMaster::create([
            'title' => $request->title,
            'opening_balance' => $request->opening_balance,
            'current_balance' => $request->opening_balance,
        ]);

        PettyCashMasterHistory::create([
            'master_id' => $master->id,
            'amount' => $request->opening_balance,
            'action' => 'opening',
            'description' => 'Opening balance added'
        ]);

        return response()->json(['success' => true, 'message' => 'Master created successfully.']);
    }

    public function edit($id)
    {
        return response()->json(PettyCashMaster::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:150',
        ]);

        $master = PettyCashMaster::findOrFail($id);
        $master->update(['title' => $request->title]);

        return response()->json(['success' => true, 'message' => 'Master updated successfully.']);
    }
}
