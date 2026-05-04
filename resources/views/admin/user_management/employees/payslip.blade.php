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
                                <select name="adjustment_type" class="form-control" required>
                                    <option value="">Select</option>
                                    <option value="1">Earning</option>
                                    <option value="2">Deduction</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label>Amount</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
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
