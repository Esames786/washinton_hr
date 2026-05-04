<?php

namespace App\Http\Controllers\PettyCash;

use App\Http\Controllers\Controller;
use App\Models\PettyCashHead;
use App\Traits\PettyCashLedgerTrait;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PettyCashLedgerController extends Controller
{

    public function index()
    {
        $heads = PettyCashHead::where('status', 1)->get();
        return view('admin.petty_cash.ledger', compact('heads'));
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $head_id = $request->get('head_id');
            $from = $request->get('from_date');
            $to = $request->get('to_date');

            $ledger = PettyCashLedgerTrait::getLedger($head_id, $from, $to);

            return DataTables::of($ledger)
                ->addIndexColumn()
                ->make(true);
        }
    }
}
