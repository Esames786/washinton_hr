@extends('layout.master')

@section('pageName', 'Employee Daily Activities')

@push('cssLinks')
    <style>
        /*.table-text-center, th { text-align: center!important; }*/
        .table-text-center, th,td {
            text-align: center!important;
        }
        .dt-input {
            padding:10px!important;
        }
        .dt-length label {
            margin-left: 10px!important;
        }
        .view-form-file{
            margin-top: 10px;
            margin-left: 10px;
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
            <table class="table table-bordered table-striped" id="activitiesTable" style="width: 100%;">
                <thead>
                <tr>
                    {{--                    <th>#</th>--}}
                    <th>Date</th>
                    <th>Field</th>
                    <th>Value</th>
{{--                    <th class="text-center">Action</th>--}}
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
                // dropdownParent: $('#AssignRoleModal .modal-body'), // modal ke andar hi render hoga
                placeholder: "-- Select Employee --",
                allowClear: true,
                width: '100%' // force full width
            });
            // DataTable
            let datatable = $('#activitiesTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                ajax: {
                    url: "{{ route('admin.employees.daily_activity_list') }}",
                    data: function (d) {
                        d.employee_ids = $('#employee_ids').val();
                        d.from_date = $('#from_date').val();
                        d.to_date   = $('#to_date').val();
                    }
                },
                columns: [
                    { data: 'employee_name', name: 'employee_name'},
                    { data: 'activity_date', name: 'activity_date' },
                    { data: 'field_name', name: 'field_name' , orderable: false, searchable: false},
                    { data: 'field_value', name: 'field_value', title: 'Value', orderable: false, searchable: false },
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
