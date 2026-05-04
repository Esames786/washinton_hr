@extends('layout.master')

@section('pageName','Payroll list')
@push('cssLinks')
    <style>
        .table-text-center, th {
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
    <div class="card h-100 p-0 radius-12">
{{--        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">--}}
{{--            <h5 class="mb-0">Payroll list</h5>--}}
{{--            <form action="{{route('admin.payroll.generate')}}" method="post">--}}
{{--                @csrf--}}
{{--                <button type="submit" class="btn btn-primary btn-sm" id="addNewBtn">--}}
{{--                    Generate Payroll--}}
{{--                </button>--}}
{{--            </form>--}}

{{--        </div>--}}
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="PayrollTable">
                    <thead>
                    <tr>
                        <th>Payroll ID</th>
                        <th>Month Year</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Note</th>
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function () {
            let datatable = $('#PayrollTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.payroll.list') }}",
                    data: function (d) {
                        if (d.search && d.search.value) {
                            d.columns[1].search.value = d.search.value;
                            d.search.value = '';
                        }
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'payroll_month', name: 'payroll_month' },
                    { data: 'from_date', name: 'from_date', orderable: false, searchable: false },
                    { data: 'to_date', name: 'to_date', orderable: false, searchable: false },
                    { data: 'notes', name: 'notes', orderable: false, searchable: false },
                    { data: 'status_id', name: 'status_id', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // ✅ delegate event for dynamic buttons
        });
    </script>

@endpush
