@extends('layout.master')

@push('cssLinks')
    <style>
        .card {
            width: 1000px;
            margin: 20px auto;
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tr><th>Basic Salary</th><td class="text-end">{{ number_format($payrollDetail->basic_salary, 2) }}</td></tr>
                <tr><th>Total Commission</th><td class="text-end">{{ number_format($payrollDetail->total_commission, 2) }}</td></tr>
                <tr><th>Employee Gratuity</th><td class="text-end">{{ number_format($payrollDetail->employee_gratuity, 2) }}</td></tr>
                <tr><th>Company Gratuity</th><td class="text-end">{{ number_format($payrollDetail->company_gratuity, 2) }}</td></tr>
                <tr><th>Total Deductions</th><td class="text-end">{{ number_format($payrollDetail->total_deductions, 2) }}</td></tr>
                @php
                    $__prodSecs = 0;
                    try {
                        $__agentId = optional($payrollDetail->employee)->agent_id;
                        $__pf = optional($payrollDetail->payroll)->from_date;
                        $__pt = optional($payrollDetail->payroll)->to_date;
                        if ($__agentId && $__pf && $__pt && \Illuminate\Support\Facades\Schema::hasTable('agent_active_times')) {
                            $__prodSecs = (int) (\Illuminate\Support\Facades\DB::table('agent_active_times')
                                ->where('user_id', $__agentId)
                                ->whereBetween('work_date', [$__pf, $__pt])
                                ->sum('active_seconds'));
                        }
                    } catch (\Throwable $e) {}
                    $__ph = intdiv($__prodSecs, 3600); $__pm = intdiv($__prodSecs % 3600, 60);
                @endphp
                <tr><th>Productive Time (this period)</th><td class="text-end">{{ $__ph }}h {{ $__pm }}m</td></tr>
                <tr>
                    <th>Net Salary</th>
                    <td id="netSalary" class="text-end"><strong>PKR {{ number_format($payrollDetail->net_salary, 2) }}</strong></td>
                </tr>
            </table>

            <!-- Adjustments -->
            <h5>Adjustments</h5>
            <table class="table table-bordered" id="adjustmentsList">
                <thead>
                <tr>
                    <th>Reason</th>
                    <th class="text-end">Amount</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payrollDetail->adjustments ?? [] as $adj)
                    <tr>
                        <td>{{ $adj->remarks }}</td>
                        <td class="text-end">
                            {{ $adj->adjustment_type == 'earning' ? '+' : '-' }}{{ number_format($adj->amount,2) }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <h5>Payslip Items</h5>
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
                        <td>{{ $item->description }}</td>
                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <a href="{{ route('employee.payslips.download',$payrollDetail->id) }}" class="btn btn-primary float-end">Download PDF</a>
        </div>
    </div>
@endsection
