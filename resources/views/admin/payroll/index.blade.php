@extends('layout.master')

@section('pageName','Generate Payroll')
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
    @include('partials.alerts')
    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Payroll</h5>
{{--            <form action="{{route('admin.payroll.generate')}}" method="post">--}}
{{--                @csrf--}}
                <button type="submit" class="btn btn-primary btn-sm" id="generatePayrollBtn">
                    Generate Payroll
                </button>
{{--            </form>--}}

        </div>
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

    <!-- Generate Payroll Modal -->
    <div class="modal fade" id="generatePayrollModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content radius-12">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="generatePayrollForm" class="form-select-2">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">From Date</label>
                                <input type="date" name="from_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">To Date</label>
                                <input type="date" name="to_date" class="form-control" required>
                            </div>
{{--                            <div class="col-md-12">--}}
{{--                                <label class="form-label">Department</label>--}}
{{--                                <select name="department_id" id="department_id" class="form-control" multiple>--}}
{{--                                    <option value="">Select Department</option>--}}
{{--                                    @foreach($departments as $department)--}}
{{--                                        <option value="{{ $department->id }}">{{ $department->name }}</option>--}}
{{--                                    @endforeach--}}
{{--                                </select>--}}
{{--                            </div>--}}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>




        $(function () {

            // Open Modal
            $('#generatePayrollBtn').click(function () {
                $('#generatePayrollForm')[0].reset();
                $('#generatePayrollModal').modal('show');
            });

            // Submit Payroll Form
            $('#generatePayrollForm').submit(function (e) {
                e.preventDefault();

                // --- Custom month validation ---
                var from = new Date($('input[name="from_date"]').val());
                var to   = new Date($('input[name="to_date"]').val());

                if(to < from){
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Dates',
                        text: 'End date cannot be before start date.'
                    });
                    return false;
                }

                var diffDays = Math.ceil((to - from) / (1000 * 60 * 60 * 24)) + 1; // include end day
                if(diffDays > 31){
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Period',
                        text: 'Payroll period cannot exceed 31 days.'
                    });
                    return false;
                }
                $.ajax({
                    url: "{{ route('admin.payroll.generate') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (response) {
                        Swal.fire(
                            'Success!',
                            response.message,
                            'success'
                        );
                        $('#generatePayrollModal').modal('hide');
                        $('#PayrollTable').DataTable().ajax.reload();
                    },
                    error: function (err) {
                        if (err.status === 422) {
                            // Laravel validation errors
                            let errors = err.responseJSON.errors;
                            let message = Object.values(errors).join("\n");
                            Swal.fire('Validation Error', message, 'error');
                        } else if (err.status === 409) {
                            // Conflict error (custom message from backend)
                            let message = err.responseJSON.message || 'Conflict occurred!';
                            Swal.fire('Conflict', message, 'warning'); // warning zyada fit hai
                        }  else {
                            // Other errors (500, 403, etc.)
                            // let msg = err.responseJSON && err.responseJSON.message
                            //     ? err.responseJSON.message
                            //     : 'Something went wrong';
                            Swal.fire('Error', 'Something went wrong', 'error');
                        }
                    }
                });
            });


            $('#department_id').select2({
                dropdownParent: $('#generatePayrollModal'),
                placeholder: "Department",
                allowClear: true,
                width: '100%' // force full width
            });

            $('input[name="from_date"]').on('change', function () {
                let fromDate = $(this).val();

                // Min hamesha fromDate
                $('input[name="to_date"]').attr('min', fromDate);

                // Max ko hata do (future allowed hai)
                $('input[name="to_date"]').removeAttr('max');

                // Agar pehle se to_date chhoti hai → reset karo
                let toDateVal = $('input[name="to_date"]').val();
                if (toDateVal && toDateVal < fromDate) {
                    $('input[name="to_date"]').val('');
                }
            });


            let datatable = $('#PayrollTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.payroll.index') }}",
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


            $(document).on('click', '.approved_btn', function () {
                let id = datatable.row($(this).closest('tr')).data().id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to approve this payroll?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Approve it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.payroll.approved') }}',
                            type: 'POST',
                            data: {
                                payroll_id: id
                            },
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire(
                                        'Approved!',
                                        response.message,
                                        'success'
                                    );
                                    datatable.ajax.reload(); // reload table without page reset
                                }
                            },
                            error: function (xhr) {
                                console.log(xhr.responseText);
                                Swal.fire(
                                    'Error!',
                                    'Something went wrong!',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.paid_btn', function () {
                let id = datatable.row($(this).closest('tr')).data().id;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to Paid this payroll?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Paid it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.payroll.paid') }}',
                            type: 'POST',
                            data: {
                                payroll_id: id
                            },
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire(
                                        'Approved!',
                                        response.message,
                                        'success'
                                    );
                                    datatable.ajax.reload(); // reload table without page reset
                                }
                            },
                            error: function (xhr) {
                                console.log(xhr.responseText);
                                Swal.fire(
                                    'Error!',
                                    'Something went wrong!',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>

@endpush
