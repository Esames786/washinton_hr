<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\GratuityBalance;
use App\Models\GratuityPayout;
use App\Models\GratuitySetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class AdminGratuityPayoutController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $gratuity_payouts = GratuityPayout::with(['employee','payout_status'])
                ->select('hr_gratuity_payouts.id','hr_gratuity_payouts.payout_date','hr_gratuity_payouts.total_balance','hr_gratuity_payouts.paid_amount','hr_gratuity_payouts.remarks','hr_gratuity_payouts.status_id','hr_gratuity_payouts.employee_id')
                ->whereIn('status_id',[1,2]);
            return DataTables::of($gratuity_payouts)
                ->addColumn('action', function ($row) {
                    $action =  '<div class="d-flex justify-content-center gap-2">';
                    if($row->status_id == 1){
                        $action.='<button type="button" class="btn btn-outline-primary-600 radius-8 px-20 py-11 approved_btn">Approved</button>';
                    } else {
                        $action.='<button type="button" class="btn btn-outline-success-600 radius-8 px-20 py-11 paid_btn">Paid</button>';
                    }
                    $action.='   <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </button>';
                    $action .='</div>';
                    return $action;
                })
                ->addColumn('employee', fn($row) => $row->employee?->full_name ?? '')
                ->editColumn('status_id', function ($row) {
                    if($row->status_id == 1){
                        return  '<span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">Pending</span>';
                    }else {
                        return  '<span class="bg-info-focus text-info-main px-24 py-4 rounded-pill fw-medium text-sm">Approved</span>';
                    }
                })
                ->filter(function ($query) {
                    if (request()->has('search') && request('search')['value'] != '') {
                        $search = request('search')['value'];

                        $query->where(function ($q) use ($search) {
                            $q->whereHas('employee', function ($sub) use ($search) {
                                $sub->where('full_name', 'like', "%{$search}%");
                            })
                                ->orWhereHas('payout_status', function ($sub) use ($search) {
                                    $sub->where('name', 'like', "%{$search}%");
                                });
                        });
                    }
                })
                ->rawColumns(['action','status_id'])
                ->make(true);
        }

        $employees = Employee::where('employee_status_id',[3,4])->get();
        return view('admin.gratuity_payouts.index')->with(['employees' => $employees]);
    }

    public function paid_list(Request $request)
    {
        if ($request->ajax()) {

            $gratuity_payouts = GratuityPayout::with(['employee','payout_status'])
                ->select('hr_gratuity_payouts.id','hr_gratuity_payouts.payout_date','hr_gratuity_payouts.total_balance','hr_gratuity_payouts.paid_amount','hr_gratuity_payouts.remarks','hr_gratuity_payouts.status_id','hr_gratuity_payouts.employee_id')
                ->where('status_id',3);
            return DataTables::of($gratuity_payouts)
                ->addColumn('employee', fn($row) => $row->employee?->full_name ?? '')
                ->editColumn('status_id', function ($row) {
                    if($row->status_id == 3){
                        return  '<span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Paid</span>';
                    }
                    return '-';
                })
                ->filter(function ($query) {
                    if (request()->has('search') && request('search')['value'] != '') {
                        $search = request('search')['value'];

                        $query->where(function ($q) use ($search) {
                            $q->whereHas('employee', function ($sub) use ($search) {
//                                $sub->where('full_name', 'like', "%{$search}%");
                                $sub->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                            })
                                ->orWhereHas('payout_status', function ($sub) use ($search) {
                                    $sub->where('name', 'like', "%{$search}%");
                                });
                        });
                    }
                })
                ->rawColumns(['status_id'])
                ->make(true);
        }

        return view('admin.gratuity_payouts.paid_list');

    }
    public function store(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'remarks' => 'nullable|string|max:255',
        ]);

        if($validate->fails()){
            return redirect()->back()->withErrors($validate);
        }

        $current_date = Carbon::now()->toDateString();
        $employee = Employee::whereIn('employee_status_id',[3,4])->where('id',$request->employee_id)->first();
        if($employee) {

            try {
                DB::beginTransaction();

                $payout =  GratuityPayout::with('payout_status')->where('employee_id',$employee->id)
                    ->where('payout_date','>=',$employee->resignation_date)->first();
                if($payout) {
                    session()->flash('error', 'Employee payout already '.$payout->payout_status->name);
                    return redirect()->back();
                }

                $gratuity = GratuitySetting::where('status', 1)->whereHas('role',function ($q) use ($employee) {
                    $q->where('role_id', $employee->role_id);
                })->first();

                if(!$gratuity) {
                    session()->flash('error', 'Gratuity settings not found!');
                    return redirect()->back();
                }

                $gratuity_payout = new gratuityPayout();
                $gratuity_payout->employee_id = $employee->id;
                $gratuity_payout->payout_date = $current_date;
                $gratuity_payout->remarks = $request->remarks;
                $gratuity_payout->status_id = 1;
                $gratuity_payout->created_by = auth('admin')->id();

                // for Provident fund
                if($gratuity->is_pf){

                    $gratuity_balance = GratuityBalance::where('employee_id',$employee->id)
                        ->where('status',0);

                    $payout_amount = $gratuity_balance->sum('closing_balance');
                    $gratuity_payout->total_balance = $payout_amount;
                    $gratuity_payout->paid_amount = $payout_amount;

                    $gratuity_balance->update([
                        'status' => 1,
                        'updated_by' => auth('admin')->id(),
                    ]);

                } else {
                    $years = Carbon::parse($employee->joining_date)
                        ->diffInYears(Carbon::parse($employee->resignation_date));

                    if($years < $gratuity->eligibility_years){
                        session()->flash('error', 'Not eligible for gratuity payouts!');
                        return redirect()->back();
                    }

                    $payout_amount = round(($employee->basic_salary * $years),2);

                    $gratuity_payout->total_balance = $payout_amount;
                    $gratuity_payout->paid_amount = $payout_amount;

                }

                $gratuity_payout->save();

                DB::commit();

                session()->flash('success', 'Payout created successfully.');
                return redirect()->back();

            } catch (\Throwable $th) {
                DB::rollBack();
                Log::channel('admin_log')->error([
                    'message' => $th->getMessage(),
                    'file'    => $th->getFile(),
                    'line'    => $th->getLine(),
                    'trace'   => $th->getTraceAsString(),
                ]);
                session()->flash('error', 'Something went wrong!');
                return redirect()->back();
            }

        }

    }

    public function edit($id)
    {
        $gratuity_payouts = GratuityPayout::where('id',$id)->where('status_id','!=',3)->first();
        if($gratuity_payouts) {
            return response()->json($gratuity_payouts);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gratuity Payout Record not found!'
        ], 404);

    }

    public function update()
    {

    }

    public function payout_approved(Request $request)
    {
        $payroll = GratuityPayout::findOrFail($request->payout_id);
        $payroll->status_id = 2;
        $payroll->save();

        return response()->json([
            'success' => true,
            'message' => 'Gratuity Payout approved successfully'
        ]);
    }

    public function payout_paid(Request $request)
    {
        $payroll = GratuityPayout::findOrFail($request->payout_id);
        $payroll->status_id = 3;
        $payroll->save();

        return response()->json([
            'success' => true,
            'message' => 'Gratuity Payout paid successfully'
        ]);
    }

}
