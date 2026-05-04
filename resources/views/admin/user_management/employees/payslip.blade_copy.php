@extends('layout.master')

@push('cssLinks')
    <style>
        .card {
            width: 1000px;
            margin: 20px auto; /* top/bottom 20px, left/right auto = center */
        }
    </style>
@endpush
@section('content')
    <div class="card">
        <div class="card-header">
            <h4>Payslip - {{ $payrollDetail->employee->full_name }}</h4>
            <strong>Month: {{ \Carbon\Carbon::createFromFormat('Y-m', $payrollDetail->payroll->payroll_month)->format('Y-F') }}</strong>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr><th>Basic Salary</th><td  class="text-end">{{ number_format($payrollDetail->basic_salary, 2) }}</td></tr>
                <tr><th>Total Commission</th><td  class="text-end">{{ number_format($payrollDetail->total_commission, 2) }}</td></tr>
                <tr><th>Employee Gratuity</th><td  class="text-end">{{ number_format($payrollDetail->employee_gratuity, 2) }}</td></tr>
                <tr><th>Company Gratuity</th><td  class="text-end">{{ number_format($payrollDetail->company_gratuity, 2) }}</td></tr>
                <tr><th>Total Deductions</th><td  class="text-end">{{ number_format($payrollDetail->total_deductions, 2) }}</td></tr>
                <tr><th >Net Salary</th><td  class="text-end"><strong>PKR {{ number_format($payrollDetail->net_salary, 2) }}</strong></td></tr>
            </table>

            <h5>Items</h5>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payrollDetail->payslipItems as $item)
                    <tr>
                        <td >{{ $item->description }}</td>
                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <a href="{{ route('admin.payroll.payslip.employee.download',$payrollDetail->id) }}" class="btn btn-primary float-end">Download PDF</a>
        </div>
    </div>
@endsection
