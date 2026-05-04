<?php

namespace App\Http\Controllers;

use App\Jobs\GeneratePayrollJob;
use App\Models\Department;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\PayrollStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class AdminPayrollController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Payroll::select([
                'id', 'payroll_month','payroll_date','from_date','to_date',
                'notes', 'status_id'
            ])
                ->whereIn('status_id', [2,3])
//                ->latest('id')
////                ->limit(1)
                ->get();

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    if ($row->status_id == 2) {
                        return '<div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn rounded-pill btn-outline-primary-600 radius-8 px-20 py-11 approved_btn">Approved</button>
                                <a href='.route('admin.payroll.payslip.show',['payroll_id' => $row->id]).' class="btn btn-outline-info-600 radius-8 px-20 py-11 view_payroll_btn">View Payroll</a>
                            </div>';
                    } else if ($row->status_id == 3) {
                        return '<div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-outline-success-600 radius-8 px-20 py-11 paid_btn">Paid</button>
                                <a href='.route('admin.payroll.payslip.show',['payroll_id' => $row->id]).' class="btn btn-outline-info-600 radius-8 px-20 py-11 view_payroll_btn">View Payroll</a>
                            </div>';
                    }
                    return '-';
                })
                ->editColumn('status_id', function ($row) {
                    if ($row->status_id == 2) {
                        return '<span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">Draft</span>';
                    } else if ($row->status_id == 3) {
                        return '<span class="bg-info-focus text-info-main px-24 py-4 rounded-pill fw-medium text-sm">Approved</span>';
                    }
                    return '-';
                })
                ->filter(function ($query) {
                    if (request()->has('search') && request('search')['value'] != '') {
                        $search = request('search')['value'];
                        $query->where('payroll_month', 'like', "%{$search}%");
                    }
                })
                ->rawColumns(['status_id', 'action'])
                ->make(true);
        }
        $departments = Department::where('status',1)->get();
        return view('admin.payroll.index',compact('departments'));
    }


   public function list(Request $request)
   {
//       if ($request->ajax()) {
//           $data = Payroll::with('payroll_status')->select([
//               'id', 'payroll_month','payroll_date','from_date','to_date',
//               'notes', 'status_id'
//           ]) ->get();
//
//           return DataTables::of($data)
//               ->addColumn('payroll_status', function ($row) {
//                   return $row->payroll_status?->name ?? '';
//               })
//               ->filter(function ($query) {
//                   if (request()->has('search') && request('search')['value'] != '') {
//                       $search = request('search')['value'];
//                       $query->where('payroll_month', 'like', "%{$search}%");
//                   }
//               })
//               ->rawColumns(['payroll_status'])
//               ->make(true);
//       }

       if ($request->ajax()) {
           $data = Payroll::select([
               'id', 'payroll_month','payroll_date','from_date','to_date',
               'notes', 'status_id'
           ])
               ->where('status_id', 4)
               ->orderBy('payroll_month');

           return DataTables::of($data)
               ->addColumn('action', function ($row) {
                   return '<div class="d-flex justify-content-center gap-2">
                                <a href='.route('admin.payroll.payslip.show',['payroll_id' => $row->id]).' class="btn btn-outline-info-600 radius-8 px-20 py-11 view_payroll_btn">View Payroll</a>
                            </div>';
               })
               ->editColumn('status_id', function ($row) {
                   if($row->status_id == 4){
                       return '<span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Paid</span>';
                   }
                   return '-';
               })
               ->filter(function ($query) {
                   if (request()->has('search') && request('search')['value'] != '') {
                       $search = request('search')['value'];
                       $query->where('payroll_month', 'like', "%{$search}%");
                   }
               })
               ->rawColumns(['status_id', 'action'])
               ->make(true);
       }

       return view('admin.payroll.list');
   }

//    public function payroll_generate(Request $request)
//    {
//        $validate = Validator::make($request->all(), [
//            'from_date' => 'required|date',
//            'to_date'   => 'required|date|after_or_equal:from_date',
//            'department_ids' => 'nullable|array',
//            'department_ids.*' => 'exists:departments,id',
//        ]);
//
//        if ($validate->fails()) {
//            return response()->json([
//                'status' => false,
//                'errors' => $validate->errors()
//            ], 422);
//        }
//
//        $paidStatusId    = 4;
//        $processStatusId = 1;
//
//        $fromDate = Carbon::parse($request->from_date);
//        $toDate   = Carbon::parse($request->to_date);
//
//        if ($fromDate->gt($toDate)) {
//            return response()->json([
//                'status' => false,
//                'message' => 'From date cannot be greater than To date.'
//            ], 422);
//        }
//
//        // Target departments → agar select kiya to wahi, warna sab
//        $departmentIds = $request->filled('department_ids')
//            ? $request->department_ids
//            : Department::pluck('id')->toArray();
//
//        // ✅ 1 Query: Already paid payrolls within range for given departments
//        $existingPayrolls = Payroll::whereIn('department_id', $departmentIds)
////            ->where('status_id', $paidStatusId)
//            ->where(function ($q) use ($fromDate, $toDate) {
//                $q->whereBetween('from_date', [$fromDate, $toDate])
//                    ->orWhereBetween('to_date', [$fromDate, $toDate])
//                    ->orWhere(function ($q2) use ($fromDate, $toDate) {
//                        $q2->where('from_date', '<=', $fromDate)
//                            ->where('to_date', '>=', $toDate);
//                    });
//            })
//            ->pluck('department_id')
//            ->toArray();
//
//        // Separate skipped and allowed
//        $skippedDepartments = Department::whereIn('id', $existingPayrolls)->pluck('name')->toArray();
//        $allowedDepartments = array_diff($departmentIds, $existingPayrolls);
//
//        if(!empty($allowedDepartments)) {
//            foreach ($allowedDepartments as $deptId) {
//                $payroll = new Payroll();
//                $payroll->payroll_month = $toDate->format('Y-m');
//                $payroll->payroll_date  = Carbon::now()->toDateString();
//                $payroll->status_id     = $processStatusId;
//                $payroll->from_date     = $fromDate->toDateString();
//                $payroll->to_date       = $toDate->toDateString();
//                $payroll->department_id = $deptId;
//                $payroll->notes         = 'Auto-generated (process)';
//                $payroll->created_by    = auth('employee')->id();
//                $payroll->save();
//
//                GeneratePayrollJob::dispatch($payroll->id);
//            }
//        }
//
//
//        if (count($skippedDepartments) > 0) {
//            if (empty($allowedDepartments)) {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'Payroll already exists for selected range in all departments: '
//                        . implode(', ', $skippedDepartments)
//                ], 409); // 409 Conflict
//            }
//
//            return response()->json([
//                'status' => true,
//                'message' => 'Payroll generated, but skipped for: '
//                    . implode(', ', $skippedDepartments)
//            ]);
//        }
//
//        return response()->json([
//            'status' => true,
//            'message' => 'Payroll generation started successfully.'
//        ]);
//    }


//   public function payroll_generate()
//   {
//;
//       $paidStatusId =  4;
//       $processStatusId =  1;
//
//       // 2) range resolve: from last PAID payroll to today
////       $lastPaid = DB::table('hr_payrolls')->where('status_id', $paidStatusId)->orderByDesc('to_date')->first();
//       $lastPaid = Payroll::where('status_id',$paidStatusId)->latest()->first();
//
//       $fromDate = $lastPaid ? Carbon::parse($lastPaid->to_date)->addDay() : Carbon::now()->startOfMonth();
//       $toDate   = Carbon::now(); // as discussed: today
//
//       // Safety: if fromDate > toDate, stop
//       if ($fromDate->gt($toDate)) {
//           return back()->with('error', 'No new days to process. Last paid payroll already up-to-date.');
//       }
//
//       // 3) create payroll (draft)
//       $payroll = new Payroll();
//       $payroll->payroll_month = $toDate->format('Y-m');
//       $payroll->payroll_date = $toDate->toDateString();
//       $payroll->status_id = $processStatusId;
//       $payroll->from_date = $fromDate->toDateString();
//       $payroll->to_date = $toDate->toDateString();
//       $payroll->notes = 'Auto-generated (process) ';
//       $payroll->created_by = auth('employee')->id();
//       $payroll->save();
//
//       $payrollId = $payroll->id;
//
//
//       // 4) dispatch job to process
//       GeneratePayrollJob::dispatch($payrollId);
//
//       return redirect()->route('admin.payroll.index', $payrollId)
//           ->with('success', 'Payroll generation started. Please refresh to see results.');
//   }
    public function payroll_generate(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()
            ], 422);
        }

        $paidStatusId    = 4;
        $processStatusId = 1;

        $fromDate = Carbon::parse($request->from_date);
        $toDate   = Carbon::parse($request->to_date);

        if ($fromDate->gt($toDate)) {
            return response()->json([
                'status' => false,
                'message' => 'From date cannot be greater than To date.'
            ], 422);
        }

        // ✅ 1 Query: Check if any payroll already exists in this date range
        $exists = Payroll::where(function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('from_date', [$fromDate, $toDate])
                ->orWhereBetween('to_date', [$fromDate, $toDate])
                ->orWhere(function ($q2) use ($fromDate, $toDate) {
                    $q2->where('from_date', '<=', $fromDate)
                        ->where('to_date', '>=', $toDate);
                });
        })->where('status_id','!=',5)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Payroll already exists for this date range.'
            ], 409);
        }

        // ✅ Create new payroll
        $payroll = new Payroll();
        $payroll->payroll_month = $toDate->format('Y-m');
        $payroll->payroll_date  = Carbon::now()->toDateString();
        $payroll->status_id     = $processStatusId;
        $payroll->from_date     = $fromDate->toDateString();
        $payroll->to_date       = $toDate->toDateString();
        $payroll->notes         = 'Auto-generated (process)';
        $payroll->created_by    = auth('employee')->id();
        $payroll->save();

        GeneratePayrollJob::dispatch($payroll->id);

        return response()->json([
            'status' => true,
            'message' => 'Payroll generation started successfully.'
        ]);
    }


    public function payroll_approve(Request $request)
    {
        try {
            DB::beginTransaction();
            $admin_id = auth('admin')->id();
            $payroll = Payroll::findOrFail($request->payroll_id);
            $payroll->status_id = 3;
            $payroll->save();

            PayrollDetail::where('payroll_id',$payroll->id)->update(['status_id' => 2]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payroll approved successfully'
            ]);

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
                'message' => 'Something went wrong while approving payroll.'
            ],500);
        }

    }

    public function payroll_paid(Request $request)
    {
        try {
            DB::beginTransaction();
            $admin_id = auth('admin')->id();
            $payroll = Payroll::findOrFail($request->payroll_id);
            $payroll->status_id = 4;
            $payroll->updated_by = $admin_id;
            $payroll->save();

            PayrollDetail::where('payroll_id',$payroll->id)->update(['status_id' => 3]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payroll Paid successfully'
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('admin_log')->error([
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Something went wrong while paid payroll.'
            ],500);
        }
    }
}
