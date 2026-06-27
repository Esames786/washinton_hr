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
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Payslip - {{ $payrollDetail->employee->full_name }}</h4>
            @if($payrollDetail->status_id == 1)
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#adjustmentModal">
                    Add Adjustment
                </button>
            @endif

        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr><th>Basic Salary</th><td class="text-end">{{ number_format($payrollDetail->basic_salary, 2) }}</td></tr>
                <tr><th>Total Commission</th><td class="text-end">{{ number_format($payrollDetail->total_commission, 2) }}</td></tr>
                <tr><th>Employee Gratuity</th><td class="text-end">{{ number_format($payrollDetail->employee_gratuity, 2) }}</td></tr>
                <tr><th>Company Gratuity</th><td class="text-end">{{ number_format($payrollDetail->company_gratuity, 2) }}</td></tr>
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
                <tr><th>Productive Time (this period)</th><td class="text-end">{{ $__ph }}h {{ $__pm }}m @if($__manual !== null)<span class="text-muted" style="font-size:11px;">(manual)</span>@endif</td></tr>
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

            <a href="{{ route('admin.payroll.payslip.employee.download',$payrollDetail->id) }}" class="btn btn-primary float-end">Download PDF</a>
        </div>
    </div>

    <!-- Adjustment Modal -->
    <div class="modal fade" id="adjustmentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title">Add Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="adjustmentForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="payroll_detail_id" value="{{ $payrollDetail->id }}">

                        <div class="row pb-8">
                            <div class="col-6">
                                <label>Adjustment Type</label>
                                <select name="adjustment_type" id="adjustment_type" class="form-control" required>
                                    <option value="">Select</option>
                                    <option value="1">Earning</option>
                                    <option value="2">Deduction</option>
                                    <option value="commission">Commission (adds to Total Commission)</option>
                                    <option value="productive">Productive Time (minutes)</option>
                                    <option value="leave">Leave from Annual (days, unpaid)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label id="amount_label">Amount</label>
                                <input type="number" step="0.01" name="amount" id="adjustment_amount" class="form-control" required>
                                <small id="amount_hint" class="text-muted"></small>
                            </div>
                        </div>

                        <div class="row pb-8">
                            <div class="col-12">
                                <label>Reason</label>
                                <textarea name="reason" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="button" class="btn btn-secondary cancel_btn" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Relabel the amount field based on the selected adjustment kind
        $(document).on('change', '#adjustment_type', function () {
            var v = $(this).val();
            var label = 'Amount', hint = '';
            if (v === 'commission') { label = 'Commission Amount (PKR)'; hint = 'Adds to Total Commission and net salary.'; }
            else if (v === 'productive') { label = 'Productive Time (minutes)'; hint = 'Shown on the payslip; no salary impact.'; }
            else if (v === 'leave') { label = 'Leave Days (from Annual)'; hint = 'Reduces annual leave balance and deducts per-day salary.'; }
            else if (v === '1') { label = 'Earning Amount (PKR)'; }
            else if (v === '2') { label = 'Deduction Amount (PKR)'; }
            $('#amount_label').text(label);
            $('#amount_hint').text(hint);
        });

        {{--$('#adjustmentForm').submit(function(e){--}}
        {{--    e.preventDefault();--}}
        {{--    let form = $(this);--}}
        {{--    $.ajax({--}}
        {{--        url: "{{ route('admin.payroll.payslip.add_adjustment') }}",--}}
        {{--        method: "POST",--}}
        {{--        data: form.serialize(),--}}
        {{--        success: function(res){--}}
        {{--            $('#adjustmentModal').modal('hide');--}}
        {{--            // append new adjustment row--}}
        {{--            $('#adjustmentsList tbody').append(--}}
        {{--                `<tr>--}}
        {{--                    <td>${res.adjustment.remarks}</td>--}}
        {{--                    <td class="text-end">${res.adjustment.payslip_item_type_id == 1 ? '+' : '-'}${res.adjustment.amount}</td>--}}
        {{--                </tr>`--}}
        {{--            );--}}
        {{--            // update net salary--}}
        {{--            $('#netSalary').html('<strong>PKR '+res.net_salary+'</strong>');--}}
        {{--        },--}}
        {{--        error: function(){--}}
        {{--            alert("Something went wrong");--}}
        {{--        }--}}
        {{--    });--}}
        {{--});--}}

        $('#adjustmentForm').submit(function(e){
            e.preventDefault();
            let form = $(this);
            if (this.checkValidity() === false) {
                e.stopImmediatePropagation(); // stop any other submit handlers
                $(form).addClass("was-validated");
                return false; // extra safety
            } else {
                $.ajax({
                    url: "{{ route('admin.payroll.payslip.add_adjustment') }}",
                    method: "POST",
                    data: form.serialize(),
                    success: function(res){
                        $('#adjustmentModal').modal('hide');

                        // append new adjustment row
                        $('#adjustmentsList tbody').append(
                            `<tr>
                                <td>${res.adjustment.remarks ?? '-'}</td>
                                <td class="text-end">
                                    ${res.adjustment.payslip_item_type_id == 1 ? '+' : '-'}
                                    ${parseFloat(res.adjustment.amount).toFixed(2)}
                                </td>
                            </tr>`
                        );

                        // update net salary
                        $('#netSalary').html('<strong>PKR '+parseFloat(res.net_salary).toFixed(2)+'</strong>');

                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: res.message,
                        });

                        // reset form
                        form[0].reset();
                    },
                    error: function(xhr){
                        if(xhr.status === 422){
                            let errors = xhr.responseJSON.errors;
                            let errorList = "<ul style='text-align:center;'>";

                            $.each(errors, function(key, messages) {
                                $.each(messages, function(index, message) {
                                    errorList += "<li>" + message + "</li>";
                                });
                            });

                            errorList += "</ul>";

                            Swal.fire({
                                icon: "error",
                                title: "Validation Error",
                                html: errorList, // text ki jaga html use karo
                            });

                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                // text: xhr.responseJSON?.message || "Something went wrong. Please try again.",
                                text: "Something went wrong. Please try again.",
                            });
                        }
                    }
                });
            }

        });

    </script>
@endpush
