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
            $payslip_employee = PayrollDetail::join('hr_payrolls', 'hr_payrolls.id', '=', 'hr_payroll_details.payroll_id')
                ->join('hr_employees', 'hr_employees.id', '=', 'hr_payroll_details.employee_id')
                ->join('hr_designations', 'hr_designations.id', '=', 'hr_employees.designation_id')
                ->join('hr_departments', 'hr_departments.id', '=', 'hr_employees.department_id')
                ->where('hr_payroll_details.employee_id', auth('employee')->id())
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
                if($request->payroll_month) {
                    $payslip_employee->where('hr_payrolls.payroll_month',$request->payroll_month);
                }else {
                    $payslip_employee->where('hr_payrolls.payroll_month',$current_month);
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
