@extends('layout.master')

@section('pageName','hr_employees')
@push('cssLinks')
    <style>
        .doc-card {
            border: 1px solid #eee;
            border-radius: 10px;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 10px;
        }

        .doc-img {
            width: 180px;      /* fixed width */
            height: 202px;     /* fixed height */
            object-fit: cover; /* maintain aspect ratio */
        }

        .doc-icon {
            font-size: 80px;
            line-height: 1;
        }

        .text-truncate {
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /*.table-text-center, th {*/
        /*    text-align: center!important;*/
        /*}*/
        .dt-input{
            padding:10px!important;
        }
        .dt-length  label {
            margin-left: 10px!important;
        }
        .table-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 400px;   /* minimum height */
            max-height: 600px;   /* maximum height */
        }

        .table-wrapper .table-responsive {
            flex: 1 1 auto;
            overflow-y: auto;    /* only table body scroll kare */
        }

        .table-wrapper .dataTables_info,
        .table-wrapper .dataTables_paginate {
            margin-top: auto;   /* pagination/info ko neeche chipka dega */
        }

    </style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-end gap-3">
                    <div class="col-md-12 col-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{route('admin.hr_employees.create')}}" class="btn btn-primary btn-sm">Add Employee</a>
                        </div>
                    </div>
                     <div class="col-md-1" style="width: 4.333333%!important"></div>
                    <div class="col-md-3 col-6 form-select-2">
                        <label class="form-label fw-semibold">Employees</label>
                        <select name="employee_ids[]" id="employee_ids" multiple class="form-select">
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label fw-semibold">Account Type</label>
                        <select name="account_type_id" id="account_type_id" class="form-select">
                            <option value="">Select Type</option>
                            @foreach($account_types as $account_type)
                                <option value="{{ $account_type->id }}">{{ $account_type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label fw-semibold">Employment Type</label>
                        <select name="employment_type_id" id="employment_type_id" class="form-select">
                            <option value="">Select Type</option>
                            @foreach($employee_types as $employee_type)
                                <option value="{{ $employee_type->id }}">{{ $employee_type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" id="search_btn" class="btn btn-primary d-flex">
                            <i class="bi bi-search"></i>  Search
                        </button>
                    </div>

        </div>
        <div class="card-body p-24 table-wrapper">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="employeesTable">
                    <thead>
                    <tr>
                        {{--                        <th scope="col">--}}
                        {{--                            <div class="d-flex align-items-center gap-10">--}}
                        {{--                                <div class="form-check style-check d-flex align-items-center">--}}
                        {{--                                    <input class="form-check-input radius-4 border input-form-dark" type="checkbox" name="checkbox" id="selectAll">--}}
                        {{--                                </div>--}}
                        {{--                                ID--}}
                        {{--                            </div>--}}
                        {{--                        </th>--}}
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Employee Code</th>
                        <th>CNIC</th>
                        <th>Agent ID</th>
                        <th>Agent Name</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Joining Date</th>
                        <th>Employment Type</th>
                        <th>Account Type</th>
                        <th>Shift</th>
                        <th>Role</th>
                        <th>Country</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Termination Modal --}}
    <div class="modal fade" id="terminationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title">Terminate Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="terminationForm">
                        <div class="row pb-8">
                            <div class="d-flex align-items-center flex-wrap gap-28 justify-content-center">
                                <div class="form-switch switch-primary d-flex align-items-center gap-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="disableEmail" name="disable_email">
                                    <label class="form-check-label line-height-1 fw-medium text-secondary-light" for="disableEmail">
                                        Disable Email
                                    </label>
                                </div>
                                <div class="form-switch switch-primary d-flex align-items-center gap-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="disablePhone" name="disable_phone">
                                    <label class="form-check-label line-height-1 fw-medium text-secondary-light" for="disablePhone">
                                        Disable Phone
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-danger">Terminate</button>
                            <button type="button" class="btn btn-secondary cancel_btn" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Document Verification Modal --}}
    <div class="modal fade" id="documentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title">Document Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="documentModalBody">
                    {{-- Documents will be loaded here via AJAX --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary cancel_btn" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Attached with day dispatch agent --}}
    <div class="modal fade" id="attachAgentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title">Attach With Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="attachAgentForm">
                        @csrf
                        <input type="hidden" name="employee_id" id="employee_id">

                        <div class="mb-3 single-form-select2">
                            <label for="agent_id" class="form-label">Select Agent</label>
                            <select name="agent_id" id="agent_id" class="form-control">
                                <option value="">-- Select Agent --</option>
                                @foreach($authorized_users as $authorized_user)
                                    <option value="{{ $authorized_user->id }}">{{ $authorized_user->id.'-'.$authorized_user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary cancel_btn" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="saveAttachAgent" class="btn btn-primary">Save</button>
                </div>
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

            $('#agent_id').select2({
                dropdownParent: $('#attachAgentModal .modal-body'), // modal ke andar hi render hoga
                placeholder: "-- Select Agent --",
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
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.hr_employees.index') }}",
                    data: function (d) {
                        d.employee_ids = $('#employee_ids').val();          // multi-select
                        d.account_type_id = $('#account_type_id').val();    // single select
                        d.employment_type_id = $('#employment_type_id').val(); // single select
                        // if (d.search && d.search.value) {
                        //     d.columns[1].search.value = d.search.value; // title column
                        //     d.search.value = ''; // clear global search
                        // }
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
                    { data: 'email', name: 'email' },
                    { data: 'employee_code', name: 'employee_code' },
                    { data: 'cnic', name: 'cnic',orderable: false },
                    { data: 'agent_id', name: 'agent_id',orderable: false, searchable: false },
                    { data: 'agent_name', name: 'agent_name',orderable: false, searchable: false },
                    { data: 'department_name', name: 'department_name' ,orderable: false, searchable: false},
                    { data: 'designation_name', name: 'designation_name',orderable: false, searchable: false },
                    { data: 'joining_date', name: 'joining_date' },
                    { data: 'employment_type_name', name: 'employment_type_name',orderable: false, searchable: false },
                    { data: 'account_type_name', name: 'account_type_name',orderable: false, searchable: false },
                    { data: 'shift_name', name: 'shift_name',orderable: false, searchable: false },
                    { data: 'role_name', name: 'role_name',orderable: false, searchable: false },
                    { data: 'country', name: 'country' ,orderable: false, searchable: false},
                    { data: 'city', name: 'city',orderable: false, searchable: false },
                    { data: 'state', name: 'state',orderable: false, searchable: false },
                    { data: 'employee_status_id', name: 'employee_status_id', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            $('#search_btn').on('click', function () {
                let employee_ids = $('#employee_ids').val();
                let account_type_id = $('#account_type_id').val();
                let employment_type_id = $('#employment_type_id').val();

                if ((!employee_ids || employee_ids.length === 0) && !account_type_id && !employment_type_id) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        html: '<b>Please select at least one filter!</b>',
                        showConfirmButton: true
                    });
                    return; // Stop reload
                }

                datatable.ajax.reload();
            });

            $(document).on('click', '.status-change', function() {
                let employeeId = datatable.row($(this).closest('tr')).data().id;
                let statusId = $(this).data('status');

                if(statusId == 3){ // Terminated
                    $('#terminationModal').data('employee-id', employeeId).modal('show');
                } else {
                    $.post("{{ route('admin.hr_employees.change-status') }}", {
                        _token: '{{ csrf_token() }}',
                        employee_id: employeeId,
                        status: statusId
                    }, function(res) {
                        if(res.success){
                            Swal.fire('Success', res.message, 'success');
                            datatable.ajax.reload();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }).fail(function(xhr){
                        Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong!', 'error');
                    });
                }
            });

            // Termination form submit
            $('#terminationForm').submit(function(e){
                e.preventDefault();
                let employeeId = $('#terminationModal').data('employee-id');
                let disableEmail = $('#disableEmail').is(':checked') ? 1 : 0;
                let disablePhone = $('#disablePhone').is(':checked') ? 1 : 0;

                $.post("{{ route('admin.hr_employees.change-status') }}", {
                    _token: '{{ csrf_token() }}',
                    employee_id: employeeId,
                    status: 3,
                    disable_email: disableEmail,
                    disable_phone: disablePhone
                }, function(res){
                    if(res.success){
                        Swal.fire('Success', res.message, 'success');
                        $('#terminationModal').modal('hide');
                        datatable.ajax.reload();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }).fail(function(xhr){
                    Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong!', 'error');
                });
            });

            // Verify checkbox AJAX
            {{--$(document).on('change', '.verify-checkbox', function() {--}}
            {{--    let docId = $(this).data('id');--}}
            {{--    let status = $(this).is(':checked') ? 1 : 0;--}}

            {{--    $.post("{{ route('admin.hr_employees.documents.verify', ':id') }}".replace(':id', docId), {--}}
            {{--        _token: '{{ csrf_token() }}',--}}
            {{--        status: status--}}
            {{--    }, function(res){--}}
            {{--        Swal.fire({--}}
            {{--            icon: res.success ? 'success' : 'error',--}}
            {{--            title: res.message,--}}
            {{--            timer: 1200,--}}
            {{--            showConfirmButton: false--}}
            {{--        });--}}
            {{--    }).fail(function(xhr) {--}}
            {{--        Swal.fire({--}}
            {{--            icon: 'error',--}}
            {{--            title:  'Something went wrong!',--}}
            {{--            timer: 1500,--}}
            {{--            showConfirmButton: false--}}
            {{--        });--}}
            {{--    });--}}
            {{--});--}}
            $(document).on('change', '.verify-checkbox', function() {
                let docId = $(this).data('id');
                let status = $(this).is(':checked') ? 1 : 0;

                let url = "{{ route('admin.hr_employees.documents.verify', ':id') }}";
                url = url.replace(':id', docId); // replace placeholder with actual ID

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: status
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: res.success ? 'success' : 'error',
                            title: res.message,
                            timer: 1200,
                            showConfirmButton: true
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Something went wrong!',
                            showConfirmButton: true
                        });
                    }
                });
            });


            // Open employee documents modal AJAX
            $(document).on('click', '.document-verification-btn', function() {
                let employeeId = $(this).data('id');

                $.get("{{ route('admin.hr_employees.documents', ':id') }}".replace(':id', employeeId), function(res) {
                    $('#documentModalBody').html(res);
                    $('#documentModal').modal('show');
                }).fail(function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to fetch documents!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                });
            });
            $(document).on('click', '.document-verification-btn', function() {
                let employeeId = $(this).data('id');

                $.get("{{ route('admin.hr_employees.documents', ':id') }}".replace(':id', employeeId), function(res) {
                    $('#documentModalBody').html(res);
                    $('#documentModal').modal('show');
                }).fail(function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to fetch documents!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                });
            });

            $(document).on("click", ".attach-agent-btn", function() {
                $('#employee_id').val('');
                let employeeId = $(this).data("id");
                $("#employee_id").val(employeeId);
                $('#agent_id').val('').trigger('change');
                $("#attachAgentModal").modal("show");
            });

            $("#saveAttachAgent").on("click", function () {
                $.ajax({
                    url: "{{ route('admin.hr_employees.attach_agent') }}",
                    method: "POST",
                    data: $("#attachAgentForm").serialize(),
                    success: function(res) {
                        $("#attachAgentModal").modal("hide");

                        if(res.status === 'success'){
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // DataTable refresh if available
                            datatable.ajax.reload();

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message,
                            });
                        }
                    },
                    error: function(err) {
                        $("#attachAgentModal").modal("hide");

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: err.responseJSON?.message || 'Something went wrong!',
                        });
                    }
                });
            });



        });

    </script>
@endpush
