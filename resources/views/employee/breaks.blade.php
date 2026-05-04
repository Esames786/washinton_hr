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

            let datatable = $('#EmployeeBreakTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('employee.breaks.index') }}",
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

        });
    </script>
@endpush
