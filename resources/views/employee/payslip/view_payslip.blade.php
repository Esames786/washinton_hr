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
    @php
        $__emp   = optional($payrollDetail->employee);
        $__isSub = $__emp->isSubcontractor();
        $__month = optional($payrollDetail->payroll)->payroll_month;
        $__monthLabel = $__month ? \Carbon\Carbon::parse($__month . '-01')->format('F Y') : null;
        // Subcontractors are worldwide and paid in USD; in-house payroll stays PKR.
        $__cur = $__isSub ? '$' : 'PKR ';
    @endphp

    {{-- ── Item 10: commission statement for subcontractors ──────────────────────────
         They are paid a percentage of the revenue they generate, so a basic/net salary
         pair describes nothing. In-house payroll below is unchanged. --}}
    @if($__isSub)
    <div class="card">
        <div class="card-body">
            <h5 class="mb-1">Earning &amp; Payouts</h5>
            @if($__monthLabel)
                <p class="text-muted mb-3" style="font-size:14px;">{{ $__monthLabel }}</p>
            @endif

            <table class="table table-bordered align-middle">
                <thead>
                    <tr><th style="width:60%;">Item</th><th class="text-end">Amount</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Revenue Generated</td>
                        <td class="text-end">
                            {{ $payrollDetail->revenue_generated !== null
                                ? $__cur . number_format((float) $payrollDetail->revenue_generated, 2)
                                : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Commission Rate</td>
                        <td class="text-end">
                            {{ $payrollDetail->commission_rate !== null
                                ? rtrim(rtrim(number_format((float) $payrollDetail->commission_rate, 2), '0'), '.') . '%'
                                : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Gross Commission</td>
                        <td class="text-end">{{ $__cur }}{{ number_format((float) $payrollDetail->total_commission, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Adjustments</td>
                        <td class="text-end">
                            @php $__adj = (float) $payrollDetail->total_deductions; @endphp
                            {{ $__adj > 0 ? '-' : '' }}{{ $__cur }}{{ number_format(abs($__adj), 2) }}
                        </td>
                    </tr>
                    <tr class="table-light">
                        <td><strong>Net Commission Payable</strong></td>
                        <td class="text-end"><strong>{{ $__cur }}{{ number_format((float) $payrollDetail->net_salary, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Payment Method</td>
                        <td class="text-end">{{ $payrollDetail->payment_method ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td>Payment Reference</td>
                        <td class="text-end">{{ $payrollDetail->payment_reference ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td class="text-end">
                            @switch($payrollDetail->status_id)
                                @case(3) <span class="badge bg-success">Paid</span> @break
                                @case(2) <span class="badge bg-info text-dark">Approved</span> @break
                                @default <span class="badge bg-warning text-dark">Pending</span>
                            @endswitch
                        </td>
                    </tr>
                </tbody>
            </table>

            <a href="{{ route('employee.payslips.index') }}" class="btn btn-outline-secondary btn-sm">
                ← Back to Earning &amp; Payouts
            </a>
        </div>
    </div>
    @else

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tr><th>Basic Pay</th><td class="text-end">{{ number_format($payrollDetail->basic_salary, 2) }}</td></tr>
                <tr><th>Total Commission</th><td class="text-end">{{ number_format($payrollDetail->total_commission, 2) }}</td></tr>
                @unless(optional($payrollDetail->employee)->isSubcontractor())
                <tr><th>Subcontractor Gratuity</th><td class="text-end">{{ number_format($payrollDetail->employee_gratuity, 2) }}</td></tr>
                <tr><th>Company Gratuity</th><td class="text-end">{{ number_format($payrollDetail->company_gratuity, 2) }}</td></tr>
                @endunless
                <tr><th>Total Deductions</th><td class="text-end">{{ number_format($payrollDetail->total_deductions, 2) }}</td></tr>
                @php
                    $__manual = $payrollDetail->manual_productive_minutes ?? null;
                    $__prodSecs = 0;
                    if ($__manual !== null) {
                        $__prodSecs = (int) $__manual * 60;
                    } else {
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
                    }
                    $__ph = intdiv($__prodSecs, 3600); $__pm = intdiv($__prodSecs % 3600, 60);
                @endphp
                <tr><th>Productive Time (this period)</th><td class="text-end">{{ $__ph }}h {{ $__pm }}m</td></tr>
                <tr>
                    <th>Net Pay</th>
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
    @endif
@endsection
