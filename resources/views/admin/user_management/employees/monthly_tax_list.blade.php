@extends('layout.master')

@section('pageName', 'Monthly Tax Report')

@push('cssLinks')
    <style>
        .table-text-center, th, td {
            text-align: center !important;
        }
        .dt-input {
            padding: 10px !important;
        }
        .dt-length label {
            margin-left: 10px !important;
        }
        #totalTaxFooter {
            font-weight: bold;
            font-size: 16px;
            margin-top: 15px;
        }
    </style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-end gap-3">
            <div class="col-md-1 col-6 "></div>
            <div class="col-md-4 col-6 form-select-2">
                <label class="form-label fw-semibold">Employees</label>
                <select name="employee_ids[]" id="employee_ids" multiple class="form-select">
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-6">
                <label class="form-label fw-semibold">Select Month</label>
                <select name="month" id="month" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            {{--            <div class="col-md-3 col-6">--}}
{{--                <label class="form-label fw-semibold">From Date</label>--}}
{{--                <input type="date" name="from_date" id="from_date" class="form-control"--}}
{{--                       max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">--}}
{{--            </div>--}}
{{--            <div class="col-md-3 col-6">--}}
{{--                <label class="form-label fw-semibold">To Date</label>--}}
{{--                <input type="date" name="to_date" id="to_date" class="form-control"--}}
{{--                       max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">--}}
{{--            </div>--}}

            <div class="col-md-1 d-grid">
                <button type="button" id="search_btn" class="btn btn-primary d-flex">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="taxTable">
                    <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Month-Year</th>
                        <th>Tax Amount</th>
                    </tr>
                    </thead>
                </table>
            </div>
            <div id="totalTaxFooter" class="text-end">
                Total Tax: PKR 0.00
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(function () {
            $('#employee_ids').select2({
                placeholder: "-- Select Employee --",
                allowClear: true,
                width: '100%'
            });

            let datatable = $('#taxTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[1, 'asc']],
                searching: true,
                ajax: {
                    url: "{{ route('admin.hr_employees.monthly_tax_list') }}", // backend route
                    data: function (d) {
                        d.employee_ids = $('#employee_ids').val();
                        d.month = $('#month').val();
                        // d.from_date = $('#from_date').val();
                        // d.to_date = $('#to_date').val();
                    }
                },
                columns: [
                    { data: 'employee', name: 'employee', orderable: true },
                    {
                        data: null,
                        name: 'month_year',
                        render: function (data) {
                            return data.month + ' ' + data.year;
                        },
                        orderable: false
                    },
                    {
                        data: 'tax_amount',
                        name: 'tax_amount',
                        className: 'text-end fw-bold',
                        render: $.fn.dataTable.render.number(',', '.', 2, 'PKR ')
                    },
                ],
                drawCallback: function (settings) {
                    if (settings.json) {
                        let totalTax = settings.json.total_tax ?? 0;
                        $('#totalTaxFooter').html('Total Tax: PKR ' + parseFloat(totalTax).toFixed(2));
                    }
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            $('#search_btn').on('click', function () {
                // let fromDate = $('#from_date').val();
                // let toDate = $('#to_date').val();
                //
                // if (!fromDate || !toDate) {
                //     Swal.fire({
                //         icon: 'warning',
                //         title: 'Missing Dates',
                //         text: 'Please select both From Date and To Date before searching.',
                //     });
                //     return false;
                // }
                //
                // if (fromDate > toDate) {
                //     Swal.fire({
                //         icon: 'error',
                //         title: 'Invalid Date Range',
                //         text: 'From Date cannot be greater than To Date.',
                //     });
                //     return false;
                // }

                datatable.ajax.reload();
            });
        });
    </script>
@endpush
