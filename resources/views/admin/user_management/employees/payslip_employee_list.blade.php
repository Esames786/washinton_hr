@extends('layout.master')

@section('pageName','Employees Payslips')
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
{{--        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">--}}
{{--            <div class="d-flex gap-2">--}}
{{--                <div class="col-md-4"></div>--}}
{{--                <form action="{{ route('admin.payslips.search') }}" method="GET" class="row g-2 align-items-end">--}}

{{--                --}}{{-- Employee Select --}}
{{--                <div class="col-md-6">--}}
{{--                    <label class="form-label fw-semibold">Employees</label>--}}
{{--                    <select name="employee_ids[]" id="employee_ids" multiple class="form-select">--}}
{{--                        @foreach($employees as $employee)--}}
{{--                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}

{{--                --}}{{-- Month-Year Select --}}
{{--                <div class="col-md-6">--}}
{{--                    <label class="form-label fw-semibold">Month-Year</label>--}}
{{--                    <select name="year_month" id="year_month" class="form-select">--}}
{{--                        @foreach($months as $month)--}}
{{--                            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}

{{--                --}}{{-- Search Button --}}
{{--                <div class="col-md-2">--}}
{{--                    <button type="submit" class="btn btn-primary">--}}
{{--                        <i class="bi bi-search"></i> Search--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--                </form>--}}
{{--            </div>--}}
{{--        </div>--}}
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-end gap-3">


           @if($search_true)
                <div class="col-md-1"></div>
                <div class="col-md-4 col-6 form-select-2">
                    <label class="form-label fw-semibold">Employees</label>
                    <select name="employee_ids[]" id="employee_ids" multiple class="form-select">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>
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
           @endif

        </div>

        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="employeesTable">
                    <thead>
                    <tr>
{{--                        <th scope="col">--}}
{{--                            <div class="d-flex align-items-center gap-10">--}}
{{--                                <div class="form-check style-check d-flex align-items-center">--}}
{{--                                    <input class="form-check-input radius-4 border input-form-dark" type="checkbox" name="checkbox" id="selectAll">--}}
{{--                                </div>--}}
{{--                                Employee ID--}}
{{--                            </div>--}}
{{--                        </th>--}}
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Employee Code</th>
                        <th>CNIC</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Basic Salary</th>
                        <th>Net Salary</th>
                        <th>Status</th>
{{--                        @if(!$search_true)--}}
                            <th>Action</th>
{{--                        @endif--}}
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
                // dropdownParent: $('#AssignRoleModal .modal-body'), // modal ke andar hi render hoga
                placeholder: "-- Select Employee --",
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
                deferLoading:0,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.payroll.payslip.list') }}",
                    data: function (d) {
                        d.employee_ids = $("#employee_ids").val();
                        d.payroll_month = $("#payroll_month").val();
                        d.payroll_id = "{{ $payroll_id ?? '' }}"; // add this line
                    }
                },
                columns: [
                    // {
                    //     data: 'id',
                    //     name: 'id',
                    //     orderable: false,
                    //     searchable: false,
                    //     render: function(data, type, row) {
                    //         return `
                    //             <div class="d-flex align-items-center gap-10">
                    //                 <div class="form-check style-check d-flex align-items-center">
                    //                     <input class="form-check-input radius-4 border border-neutral-400 row-checkbox" type="checkbox" name="checkbox" value="${row.id}">
                    //                 </div>
                    //                 ${row.id}
                    //             </div>
                    //         `;
                    //     }
                    // },
                    { data: 'id', name: 'id' },
                    { data: 'full_name', name: 'full_name',orderable: false },
                    { data: 'employee_code', name: 'employee_code' },
                    { data: 'cnic', name: 'cnic',orderable: false, searchable: false },
                    { data: 'department_name', name: 'department_name' ,orderable: false, searchable: false},
                    { data: 'designation_name', name: 'designation_name',orderable: false, searchable: false },
                    { data: 'basic_salary', name: 'basic_salary',orderable: false, searchable: false },
                    { data: 'net_salary', name: 'net_salary',orderable: false, searchable: false },
                    { data: 'status_id', name: 'status_id',orderable: false, searchable: false },
{{--                    @if(!$search_true)--}}
                    { data: 'action', name: 'action', orderable: false, searchable: false }
{{--                    @endif--}}

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
