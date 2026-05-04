@extends('layout.master')

@section('pageName', 'Attendance list')

@push('cssLinks')
    <style>
        .table-text-center, th,td {
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
            <div class="col-md-2 col-4">

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
            <table class="table table-bordered table-striped" id="EmployeeAttendanceTable" style="width: 100%;">
                <thead>
                <tr>
                    <th>Attendance Date</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Working Hours</th>
                    <th>Status</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>



@endsection

@push('scripts')


    <script>
        $(function() {

            let datatable = $('#EmployeeAttendanceTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('employee.attendance.index') }}",
                    data: function (d) {
                        d.from_date = $('#from_date').val();
                        d.to_date   = $('#to_date').val();
                    }
                },
                columns: [
                    { data: 'attendance_date', name: 'attendance_date', orderable: true },
                    { data: 'check_in', name: 'check_in', orderable: false },
                    { data: 'check_out', name: 'check_out', orderable: false },
                    { data: 'working_hours', name: 'working_hours', orderable: false },
                    { data: 'attendance_status_name', name: 'attendance_status_name', orderable: false, searchable: false },
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            $('#search_btn').on('click', function () {
                let fromDate = $('#from_date').val();
                let toDate = $('#to_date').val();

                if (!fromDate || !toDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Dates',
                        text: 'Please select both From Date and To Date before searching.',
                    });
                    return false;
                }

                if (fromDate > toDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date Range',
                        text: 'From Date cannot be greater than To Date.',
                    });
                    return false;
                }

                datatable.ajax.reload();
            });
        });
    </script>
@endpush
