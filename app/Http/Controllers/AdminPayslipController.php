<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\PayslipAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;



class AdminPayslipController extends Controller
{
    public function index(Request $request)
    {

        $search_true = true;
//        if($request->has('payroll_id')){
//            $payroll = Payroll::where('id', $request->payroll_id)->whereNotIn('status_id',[4]);
//
//            if(!$payroll->exists()){
//                return redirect()->route('admin.not_found');
//            }
//            $search_true=false;
//        }

        $months = collect(range(0, 11))->map(function ($i) {
            $date = now()->subMonths($i);
            return [
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y')
            ];
        });
        $employees = Employee::where('employee_status_id',1)->select('id','full_name')->get();
        return view('admin.user_management.employees.payslip_employee_list', compact('months', 'employees', 'search_true'));
    }

    public function show($payroll_id)
    {
        $search_true=false;
        $employees = Employee::where('employee_status_id',1)->select('id','full_name')->get();
        return view('admin.user_management.employees.payslip_employee_list', compact('employees', 'search_true'));
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {

            // Eager load relations and select necessary columns
            $payslip_employee = PayrollDetail::join('hr_payrolls', 'hr_payrolls.id', '=', 'hr_payroll_details.payroll_id')
                ->join('hr_employees', 'hr_employees.id', '=', 'hr_payroll_details.employee_id')
                ->join('hr_designations', 'hr_designations.id', '=', 'hr_employees.designation_id')
                ->join('hr_departments', 'hr_departments.id', '=', 'hr_employees.department_id')
                ->select(
                    'hr_payroll_details.id as payroll_detail_id',
                    'hr_payroll_details.payroll_id',
                    'hr_employees.id',
                    'hr_employees.full_name',
                    'hr_employees.email',
                    'hr_employees.employee_code',
                    'hr_employees.cnic',
                    'hr_employees.department_id',
                    'hr_departments.name as department_name',
                    'hr_designations.name as designation_name',
                    'hr_employees.designation_id',
                    'hr_payroll_details.basic_salary',
                    'hr_payroll_details.net_salary',
                    'hr_payroll_details.status_id'
                );
            if($request->payroll_id){
                $payslip_employee->where('hr_payrolls.id', $request->payroll_id);
            } elseif (!empty($request->employee_ids)) {
                $payslip_employee->whereIn('hr_payroll_details.employee_id', $request->employee_ids);
            }
            else {
                $payslip_employee ->where('hr_payrolls.payroll_month',$request->payroll_month);
            }

            return DataTables::of($payslip_employee)
                ->editColumn('status_id', function ($row) {
                    switch ($row->status_id) {
                        case 1:
                            return '<span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">Pending</span>';
                        case 2:
                            return '<span class="bg-info-focus text-info-main px-24 py-4 rounded-pill fw-medium text-sm">Approved</span>';
                        case 3:
                            return '<span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Paid</span>';
                        default:
                            return '-';
                    }
                })
                ->addColumn('action', function ($row) use ($request) {
                    $action = '<div class="d-flex justify-content-center gap-2">';
                    $action .= '<a href="' . route('admin.payroll.payslip.employee', ['id' => $row->payroll_detail_id]) . '" class="btn btn-outline-primary-600 radius-8 px-20 py-11" style="width: 140px;">View Payslip</a>';
                    $action .= '</div>';
                    return $action;
                })
                ->filter(function ($query) {
                    if ($search = request('search')['value'] ?? false) {
                        $query->where(function ($q) use ($search) {
                            $q->where('hr_employees.full_name', 'like', "%{$search}%")
                                ->orWhere('hr_employees.employee_code', 'like', "%{$search}%")
                                ->orWhere('cnic', 'like', "%{$search}%");
                        });
                    }
                })
                ->rawColumns(['action','status_id'])
                ->make(true);
        }
    }

    public function payslip_show($id)
    {

        $payrollDetail = PayrollDetail::with(['employee', 'payroll', 'payslipItems','adjustments'])->where('id', $id)->first();
        if($payrollDetail) {
            return view('admin.user_management.employees.payslip', compact('payrollDetail'));
        }
        return redirect()->route('admin.not_found');
    }

    public function payslip_download($id)
    {
        $payrollDetail = PayrollDetail::with(['employee', 'payroll', 'payslipItems'])->findOrFail($id);

        $pdf = Pdf::loadView('admin.user_management.employees.payslip_download', compact('payrollDetail'));
        return $pdf->download("Payslip_{$payrollDetail->employee->name}_{$payrollDetail->payroll->payroll_month}.pdf");
    }

//    public function store(Request $request, PayrollDetail $payrollDetail)
//    {
//        $request->validate([
//            'adjustments' => 'required|array',
//            'adjustments.*.type' => 'required|string|in:addition,deduction',
//            'adjustments.*.amount' => 'required|numeric|min:0',
//            'adjustments.*.remarks' => 'nullable|string',
//        ]);
//
//        foreach ($request->adjustments as $adj) {
//            $payrollDetail->adjustments()->create([
//                'type' => $adj['type'],
//                'amount' => $adj['amount'],
//                'remarks' => $adj['remarks'] ?? null,
//            ]);
//        }
//
//        // Recalculate net salary
//        $netSalary = ($payrollDetail->basic_salary + $payrollDetail->total_commission + $payrollDetail->employee_gratuity + $payrollDetail->company_gratuity)
//            - $payrollDetail->total_deductions;
//
//        $addition = $payrollDetail->adjustments()->where('type', 'addition')->sum('amount');
//        $deduction = $payrollDetail->adjustments()->where('type', 'deduction')->sum('amount');
//
//        $payrollDetail->net_salary = $netSalary + $addition - $deduction;
//        $payrollDetail->save();
//
//        return response()->json(['message' => 'Adjustments added successfully', 'net_salary' => $payrollDetail->net_salary]);
//    }

    public function add_adjustment(Request $request)
    {

        // adjustment_type may be a payslip item type id ('1' earning / '2' deduction)
        // OR one of the special kinds: 'commission', 'productive', 'leave'.
        $validator = Validator::make($request->all(), [
            'adjustment_type'   => 'required|string',
            'amount'            => 'required|numeric|min:0',
            'reason'            => 'nullable|string',
            'payroll_detail_id' => 'required|integer',
        ]);

        if($validator->fails()) {
                return response()->json([
                    'message' => 'Validation Failed',
                    'errors' => $validator->errors(),
                ],422);
        }

        $kind   = (string) $request->adjustment_type;
        $amount = (float) $request->amount;

        DB::beginTransaction();

        try {

            $payroll_detail = PayrollDetail::where('id',$request->payroll_detail_id)->where('status_id',1)->first();
            if($payroll_detail) {

                $payslip_adjustment = null;

                if ($kind === 'commission') {
                    // Manual commission — adds to Total Commission (raises net via base formula)
                    $payroll_detail->total_commission = ($payroll_detail->total_commission ?? 0) + $amount;

                } elseif ($kind === 'productive') {
                    // Manual productive time (minutes) — display only, no pay impact
                    $payroll_detail->manual_productive_minutes = (int) round($amount);

                } elseif ($kind === 'leave') {
                    // Unpaid leave from Annual: reduce annual quota AND deduct per-day salary
                    $days  = $amount;
                    $perDay = ($payroll_detail->basic_salary ?? 0) / 30;
                    $deductAmount = round($perDay * $days, 2);

                    // Deduct salary (record as a type-2 deduction adjustment)
                    $payslip_adjustment = new PayslipAdjustment();
                    $payslip_adjustment->payroll_detail_id    = $request->payroll_detail_id;
                    $payslip_adjustment->payslip_item_type_id = 2; // deduction
                    $payslip_adjustment->amount               = $deductAmount;
                    $payslip_adjustment->remarks              = ($request->reason ? $request->reason . ' — ' : '') . 'Unpaid Annual Leave: ' . rtrim(rtrim(number_format($days,2),'0'),'.') . ' day(s)';
                    $payslip_adjustment->created_by           = auth('admin')->id();
                    $payslip_adjustment->save();

                    // Reduce the employee's Annual Leave (type 3) balance
                    DB::table('hr_employee_assign_leaves')
                        ->where('employee_id', $payroll_detail->employee_id)
                        ->where('leave_type_id', 3)
                        ->where('status', 1)
                        ->orderByDesc('id')
                        ->limit(1)
                        ->update(['used_quota' => DB::raw('used_quota + ' . (int) ceil($days)), 'updated_at' => now()]);

                } else {
                    // Standard earning ('1') / deduction ('2') line item
                    if (!in_array($kind, ['1','2'], true)) {
                        DB::rollBack();
                        return response()->json(['message' => 'Invalid adjustment type.'], 422);
                    }
                    $payslip_adjustment = new PayslipAdjustment();
                    $payslip_adjustment->payroll_detail_id    = $request->payroll_detail_id;
                    $payslip_adjustment->payslip_item_type_id = (int) $kind;
                    $payslip_adjustment->amount               = $amount;
                    $payslip_adjustment->remarks              = $request->reason;
                    $payslip_adjustment->created_by           = auth('admin')->id();
                    $payslip_adjustment->save();
                }

                // Recalculate net salary (commission folds into the base; adjustments add/deduct)
                $base_net_salary = ($payroll_detail->basic_salary + $payroll_detail->total_commission + $payroll_detail->employee_gratuity + $payroll_detail->company_gratuity)
                    - $payroll_detail->total_deductions;

                $addition  = $payroll_detail->adjustments()->where('payslip_item_type_id', '1')->where('status',1)->sum('amount');
                $deduction = $payroll_detail->adjustments()->where('payslip_item_type_id', '2')->where('status',1)->sum('amount');

                $payroll_detail->net_salary = $base_net_salary + $addition - $deduction;
                $payroll_detail->save();

                DB::commit();
                return response()->json([
                    'message' => 'Adjustments added successfully',
                    'net_salary' => $payroll_detail->net_salary,
                    'adjustment' => $payslip_adjustment
                ]);
            }

            return response()->json([
                'message' => 'Employee Payslip not found',
            ],404);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('admin_log')->error([
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Something went wrong',
            ],500);
        }

    }

}
