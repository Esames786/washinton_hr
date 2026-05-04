<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\PayrollDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class EmployeePayslipController extends Controller
{

    public function index(Request $request)
    {

        $months = collect(range(0, 11))->map(function ($i) {
            $date = now()->subMonths($i);
            return [
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y')
            ];
        });

        return view('employee.payslip.index',compact('months'));
    }
    public function list(Request $request)
    {

        if ($request->ajax()) {

            $current_month = Carbon::now()->format('Y-m');
            // Eager load relations and select necessary columns
            $payslip_employee = PayrollDetail::join('payrolls', 'payrolls.id', '=', 'payroll_details.payroll_id')
                ->join('employees', 'employees.id', '=', 'payroll_details.employee_id')
                ->join('designations', 'designations.id', '=', 'employees.designation_id')
                ->join('departments', 'departments.id', '=', 'employees.department_id')
                ->where('payroll_details.employee_id', auth('employee')->id())
                ->select(
                    'payroll_details.id as payroll_detail_id',
                    'payroll_details.payroll_id',
                    'employees.id',
                    'employees.full_name',
                    'employees.email',
                    'employees.employee_code',
                    'employees.cnic',
                    'employees.department_id',
                    'departments.name as department_name',
                    'designations.name as designation_name',
                    'employees.designation_id',
                    'payroll_details.basic_salary',
                    'payroll_details.net_salary',
                    'payroll_details.status_id'
                );
                if($request->payroll_month) {
                    $payslip_employee->where('payrolls.payroll_month',$request->payroll_month);
                }else {
                    $payslip_employee->where('payrolls.payroll_month',$current_month);
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
                    $action .= '<a href="' . route('employee.payslips.show', ['id' => $row->payroll_detail_id]) . '" class="btn btn-outline-primary-600 radius-8 px-20 py-11" style="width: 140px;">View Payslip</a>';
                    $action .= '</div>';
                    return $action;
                })
                ->filter(function ($query) {
                    if ($search = request('search')['value'] ?? false) {
                        $query->where(function ($q) use ($search) {
                            $q->where('employees.full_name', 'like', "%{$search}%")
                                ->orWhere('employees.employee_code', 'like', "%{$search}%")
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

            return view('employee.payslip.view_payslip', compact('payrollDetail'));
        }
        return redirect()->route('admin.not_found');
    }

    public function payslip_download($id)
    {
        $payrollDetail = PayrollDetail::with(['employee', 'payroll', 'payslipItems'])->findOrFail($id);

        $pdf = Pdf::loadView('employee.payslip.payslip_download', compact('payrollDetail'));
        return $pdf->download("Payslip_{$payrollDetail->employee->name}_{$payrollDetail->payroll->payroll_month}.pdf");
    }
}
