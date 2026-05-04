@extends('layout.master')

@section('pageName', 'Break List')

@push('cssLinks')
    <style>
        .table-text-center, th, td {
            text-align: center!important;
        }
        .dt-input {
            padding:10px!important;
        }
        .dt-length label {
            margin-left: 10px!important;
        }
    </style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-end gap-3">
            <div class="col-md-4 col-6 form-select-2">
                <label class="form-label fw-semibold">Employees</label>
                <select name="employee_ids[]" id="employee_ids" multiple class="form-select">
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label fw-semibold">From Date</label>
                <input type="date" name="from_date" id="from_date" class="form-control"
                       max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label fw-semibold">To Date</label>
                <input type="date" name="to_date" id="to_date" class="form-control"
                       max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            </div>

            <div class="col-md-1 d-grid">
                <button type="button" id="search_btn" class="btn btn-primary d-flex">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="EmployeeBreakTable" style="width: 100%;">
                <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Break Start</th>
                    <th>Break End</th>
                    <th>Break Duration</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(function() {

            $('#employee_ids').select2({
                placeholder: "-- Select Employee --",
                allowClear: true,
                width: '100%' // force full width
            });

            let datatable = $('#EmployeeBreakTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.hr_employees.break_list') }}",
                    data: function (d) {
                        d.employee_ids = $('#employee_ids').val();
                        d.from_date = $('#from_date').val();
                        d.to_date   = $('#to_date').val();
                    }
                },
                columns: [
                    { data: 'employee_name', name: 'employee_name', orderable: true },
                    { data: 'break_start', name: 'break_start', orderable: true },
                    { data: 'break_end', name: 'break_end', orderable: true },
                    { data: 'break_duration', name: 'break_duration', orderable: false },
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            $('#search_btn').on('click', function () {
                // let fromDate = $('#from_date').val();
                // let toDate = $('#to_date').val();
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
