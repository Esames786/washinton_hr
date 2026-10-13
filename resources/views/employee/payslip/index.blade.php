@extends('layout.master')

@section('pageName','Earning & Payouts')
@push('cssLinks')
    <style>
        .table-text-center, th {
            text-align: center!important;
        }
        .dt-input{
            padding:10px!important;
        }
        .dt-length  label {
            margin-left: 10px!important;
        }
    </style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-end gap-3">
                <div class="col-md-3 col-6"></div>
                <div class="col-md-4 col-6">
                    <label class="form-label fw-semibold">Month-Year</label>
                    <select name="payroll_month" id="payroll_month" class="form-select">
                        <option value="">Select Month</option>
                        @foreach($months as $month)
                            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button type="button" id="search_btn" class="btn btn-primary d-flex">
                        <i class="bi bi-search"></i>  Search
                    </button>
                </div>
        </div>

        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="employeesTable">
                    <thead>
                    <tr>
                        {{-- Item 10: this is the agent's OWN statement, so their id, name,
                             CNIC, department and designation told them nothing. Replaced with the
                             commission figures that actually matter to them. --}}
                        <th>Month</th>
                        <th>Revenue Generated</th>
                        <th>Commission Rate</th>
                        <th>Commission</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            $('#employee_ids').select2({
                placeholder: "-- Select Subcontractor --",
                allowClear: true,
                width: '100%' // force full width
            });
            // 1. Init DataTable
            let datatable = $('#employeesTable').DataTable({
                processing: true,
                serverSide: true,
                // order: [],
                // [1, 'asc']
                searching: true,
                // deferLoading:0,
                rowId: 'id',
                ajax: {
                    url: "{{ route('employee.payslips.list') }}",
                    data: function (d) {
                        d.employee_ids = $("#employee_ids").val();
                        d.payroll_month = $("#payroll_month").val();
                        d.payroll_id = "{{ $payroll_id ?? '' }}"; // add this line
                    }
                },
                columns: [
                    { data: 'payroll_month', name: 'hr_payrolls.payroll_month' },
                    { data: 'revenue_generated', name: 'revenue_generated', orderable: false, searchable: false },
                    { data: 'commission_rate', name: 'commission_rate', orderable: false, searchable: false },
                    { data: 'total_commission', name: 'total_commission', orderable: false, searchable: false },
                    { data: 'payment_method', name: 'payment_method', orderable: false, searchable: false },
                    { data: 'payment_reference', name: 'payment_reference', orderable: false, searchable: false },
                    { data: 'status_id', name: 'status_id',orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }

                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            $("#search_btn").on('click',function (){
                let payroll_month = $("#payroll_month").val();
                let employee_ids = $("#employee_ids").val();

                if (payroll_month === '' || payroll_month <= 0) {
                    toastr.error('Must select payroll month', 'Error!', {
                        positionClass: 'toast-top-center'
                    });
                    return;
                }else{
                    datatable.ajax.reload();
                }

            });

        });

    </script>
@endpush
