<?php

namespace App\Http\Controllers\PettyCash;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PettyCashHead;
use App\Models\PettyCashMaster;
use App\Models\PettyCashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class PettyCashTransactionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PettyCashTransaction::with(['head', 'master'])
                ->select(['id', 'master_id', 'head_id', 'date', 'entry_type', 'amount', 'description','balance','status','image'])->orderBy('id');

            return DataTables::of($data)
                ->addColumn('head', fn($row) => $row->head->name ?? '-')
                ->addColumn('master', fn($row) => $row->master->title ?? '-')
                ->editColumn('entry_type', fn($row) => ucfirst($row->entry_type))
                ->addColumn('account_type', function ($row) {
                    if ($row->head?->type === 'income') {
                        return '<span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Income</span>';
                    } elseif ($row->head?->type === 'expense') {
                        return '<span class="bg-danger-focus text-danger-main px-24 py-4 rounded-pill fw-medium text-sm">Expense</span>';
                    } else {
                        return '<span class="bg-secondary text-dark px-24 py-4 rounded-pill fw-medium text-sm">Other</span>';
                    }
                })
                ->addColumn('image', function ($row) {
                    if ($row->image) {
                        $url = asset($row->image);
                        return '<a href="'.$url.'" target="_blank">
                            <img src="'.$url.'" width="50" height="50" style="object-fit:cover; border-radius:4px;"/>
                        </a>';
                    }
                    return '-';
                })
                ->editColumn('balance', function ($row){
                    $balance = number_format($row->balance, 2);
                    return $balance;
                })
                ->editColumn('amount', function ($row) {
                    $sign = '';
                    $amount = number_format($row->amount, 2);

                    if ($row->head?->type === 'expense') {
                        // Expense -> cash goes out
                        $sign = ($row->entry_type === 'debit') ? '-' : '+';
                    } elseif ($row->head?->type === 'income') {
                        // Income -> cash comes in
                        $sign = ($row->entry_type === 'credit') ? '+' : '-';
                    }

                    return $sign . $amount;
                })
                ->addColumn('status', function ($row) {
                    $status = $row->status ?? 'pending'; // agar null ho to default 'pending'

                    if ($status === 'approved') {
                        return '<span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Approved</span>';
                    } elseif ($status === 'rejected') {
                        return '<span class="bg-danger-focus text-danger-main px-24 py-4 rounded-pill fw-medium text-sm">Rejected</span>';
                    } else {
                        return '<span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">Pending</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    if ($row->status === 'pending') {
                        return '
                            <button type="button" class="btn btn-outline-success-600 px-20 py-11 approve_btn" data-id="'.$row->id.'">Approve</button>
                            <button type="button" class="btn btn-outline-danger-600 px-20 py-11 reject_btn" data-id="'.$row->id.'">Reject</button>
                        ';
                    } else if($row->status === 'approved') {
                        $printUrl = route('admin.petty_cash.transactions.print', $row->id);
                        return ' <a href="'.$printUrl.'" target="_blank" class="btn btn-outline-primary-600 px-20 py-11">Print</a>';
                    }
                    return '-';
                })
                ->rawColumns(['account_type','action','amount','status','image'])
                ->make(true);
        }

        return view('admin.petty_cash.transactions');
    }

//    public function store(Request $request)
//    {
//        $request->validate([
//            'master_id' => 'required|exists:petty_cash_masters,id',
//            'head_id' => 'required|exists:petty_cash_heads,id',
//            'date' => 'required|date',
//            'entry_type' => 'required|in:debit,credit',
//            'amount' => 'required|numeric|min:0.01',
//        ]);
//
//        $transaction = PettyCashTransaction::create($request->all());
//
//        // Update balance
//        $master = PettyCashMaster::find($request->master_id);
//        if ($request->entry_type == 'credit') {
//            $master->current_balance += $request->amount;
//        } else {
//            $master->current_balance -= $request->amount;
//        }
//        $master->save();
//
//        return response()->json(['success' => true, 'message' => 'Transaction added successfully.']);
//    }

    public function store(Request $request)
    {
        $request->validate([
            'master_id' => 'required|exists:hr_petty_cash_masters,id',
            'head_id' => 'required|exists:hr_petty_cash_heads,id',
            'date' => 'required|date',
            'entry_type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {

            $master = PettyCashMaster::findOrFail($request->master_id);
            $head = PettyCashHead::findOrFail($request->head_id); // ✅ missing

            if (
                ($head->type == 'expense' && $request->entry_type == 'debit') ||
                ($head->type == 'income' && $request->entry_type == 'debit')
            ) {
                if ($master->current_balance < $request->amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient funds in master account.'
                    ], 422);
                }
            }

            // Update master balance based on head type
//            if ($head->type == 'expense') {
//                if ($request->entry_type == 'debit') {
//                    $master->current_balance -= $request->amount; // expense = cash goes out
//                } else {
//                    $master->current_balance += $request->amount; // refund / reversal
//                }
//            } elseif ($head->type == 'income') {
//                if ($request->entry_type == 'credit') {
//                    $master->current_balance += $request->amount; // income = cash comes in
//                } else { // debit = reversal / refund
//                    $master->current_balance -= $request->amount;
//                }
//            }
//            $master->save();

            $imagePath=null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();

                // Direct public/uploads/petty_cash folder me save hoga
                $destination = public_path('Uploads/petty_cash');
                if (!file_exists($destination)) {
                    mkdir($destination, 0777, true); // folder auto create if not exists
                }

                $file->move($destination, $filename);

                // Path ko DB me store karne ke liye
                $imagePath = 'Uploads/petty_cash/' . $filename;
            }

            // Save transaction
            $transaction = PettyCashTransaction::create([
                'master_id'   => $request->master_id,
                'head_id'     => $request->head_id,
                'date'        => $request->date,
                'entry_type'  => $request->entry_type,
                'amount'      => $request->amount,
                'description' => $request->description,
                'balance'     => 0,
                'image'       => $imagePath,
                'created_by'  => auth('admin')->id(),
                'status'      => 'pending',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction added successfully.']);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('admin_log')->error([
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ],500);
        }

    }


    public function approveTransaction($id)
    {
        DB::beginTransaction();
        try {
            $txn = PettyCashTransaction::findOrFail($id);

            if ($txn->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Transaction is not pending.'], 422);
            }

            $master = $txn->master;
            $head = $txn->head;

            // Balance check for debit
            if (
                ($head->type == 'expense' && $txn->entry_type == 'debit') ||
                ($head->type == 'income' && $txn->entry_type == 'debit')
            ) {
                if ($master->current_balance < $txn->amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient funds in master account.'
                    ], 422);
                }
            }

            // Apply effect on master balance
            if ($head->type == 'expense') {
                $master->current_balance += ($txn->entry_type === 'debit' ? -$txn->amount : $txn->amount);
            } elseif ($head->type == 'income') {
                $master->current_balance += ($txn->entry_type === 'credit' ? $txn->amount : -$txn->amount);
            }
            $master->save();

            // Update transaction status and balance
            $txn->update([
                'status'  => 'approved',
                'balance' => $master->current_balance,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction approved successfully.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('admin_log')->error([
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    public function rejectTransaction($id)
    {
        $txn = PettyCashTransaction::findOrFail($id);
        if ($txn->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Transaction is not pending.'], 422);
        }
        $txn->update(['status' => 'rejected']);
        return response()->json(['success' => true, 'message' => 'Transaction rejected successfully.']);
    }

    public function edit($id)
    {
        return response()->json(PettyCashTransaction::findOrFail($id));
    }

//    public function update(Request $request, $id)
//    {
//        $request->validate([
//            'master_id' => 'required|exists:petty_cash_masters,id',
//            'head_id' => 'required|exists:petty_cash_heads,id',
//            'date' => 'required|date',
//            'entry_type' => 'required|in:debit,credit',
//            'amount' => 'required|numeric|min:0.01',
//        ]);
//
//        DB::beginTransaction();
//        try {
//            $txn = PettyCashTransaction::findOrFail($id);
//            $master = $txn->master;
//            $oldHead = $txn->head; // previous head
//            $newHead = PettyCashHead::findOrFail($request->head_id); // new head
//
//            // ✅ Reverse old effect based on old head type
//            if ($oldHead->type == 'expense') {
//                if ($txn->entry_type == 'debit') {
//                    $master->current_balance += $txn->amount; // reverse cash out
//                } else {
//                    $master->current_balance -= $txn->amount; // reverse refund
//                }
//            } elseif ($oldHead->type == 'income') {
//                if ($txn->entry_type == 'credit') {
//                    $master->current_balance -= $txn->amount; // reverse cash in
//                } else {
//                    $master->current_balance += $txn->amount; // reverse refund
//                }
//            }
//
//            // ✅ Check balance before applying new
//            if (
//                ($newHead->type == 'expense' && $request->entry_type == 'debit') ||
//                ($newHead->type == 'income' && $request->entry_type == 'debit')
//            ) {
//                if ($master->current_balance < $request->amount) {
//                    return response()->json([
//                        'success' => false,
//                        'message' => 'Insufficient funds in master account.'
//                    ], 422);
//                }
//            }
//
//            // ✅ Update transaction
//            $txn->update($request->all());
//
//            // ✅ Apply new effect based on new head type
//            if ($newHead->type == 'expense') {
//                if ($txn->entry_type == 'debit') {
//                    $master->current_balance -= $txn->amount; // cash out
//                } else {
//                    $master->current_balance += $txn->amount; // refund
//                }
//            } elseif ($newHead->type == 'income') {
//                if ($txn->entry_type == 'credit') {
//                    $master->current_balance += $txn->amount; // cash in
//                } else {
//                    $master->current_balance -= $txn->amount; // refund
//                }
//            }
//
//            $master->save();
//
//            // ✅ Update transaction balance
//            $txn->update(['balance' => $master->current_balance]);
//            DB::commit();
//            return response()->json(['success' => true, 'message' => 'Transaction updated successfully.']);
//
//        }catch (\Throwable $th) {
//            DB::rollBack();
//            Log::channel('admin_log')->error([
//                'message' => $th->getMessage(),
//                'file'    => $th->getFile(),
//                'line'    => $th->getLine(),
//                'trace'   => $th->getTraceAsString(),
//            ]);
//            return response()->json([
//                'success' => false,
//                'message' => 'Something went wrong'
//            ],500);
//        }
//
//    }

    public function printInvoice($id)
    {
        $txn = PettyCashTransaction::with(['head','master'])->findOrFail($id);

        return view('admin.petty_cash.print_invoice', compact('txn'));
    }

    public function payroll_list($head_id) {
        if($head_id == 1) {
            $payroll_list = Payroll::where('status_id',2)
                ->select(['id','payroll_month','total_deduction','total_net_salary'])
                ->get();
            return response()->json(['status'=>1,'payroll_list'=>$payroll_list]);
        }
        return response()->json(['status'=>0,'payroll_list'=>[]]);
    }
}
