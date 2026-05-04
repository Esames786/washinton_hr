@extends('layout.master')

@section('pageName','Attendance Types')
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
{{--            <h5 class="mb-0">Attendance Types</h5>--}}
{{--            <div class="d-flex gap-2">--}}
{{--                <button class="btn btn-primary btn-sm" id="addNewBtn">--}}
{{--                    Add Attendance Type--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </div>--}}
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="attendanceTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Status</th>
{{--                        <th>Action</th>--}}
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    <div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Attendance Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.attendance_types.store')}}" method="post" id="attendanceForm" class="needs-validation" novalidate>
                        @csrf

                        <input type="hidden" name="id" id="attendanceId">

                        <div class="row pb-8">
                            <div class="col-12">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>

                        <div class="row pb-8 d-none" id="status_div">
                            <div class="col-12">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-secondary cancel_btn" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            // init datatable
            let datatable = $('#attendanceTable').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.attendance_types.index') }}",
                    data: function (d) {
                        if (d.search && d.search.value) {
                            d.columns[1].search.value = d.search.value; // name column
                            d.search.value = ''; // clear global search
                        }
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name', orderable: false },
                    { data: 'status', name: 'status', orderable: false },
                    // { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // add new
            $('#addNewBtn').click(function(){
                $('#attendanceForm')[0].reset();
                $('#attendanceId').val('');
                $('#modalTitle').text('Add Attendance Type');
                $('#attendanceForm').attr('action', "{{ route('admin.attendance_types.store') }}");
                $('#attendanceForm input[name="_method"]').remove();
                $('#status_div').addClass('d-none');
                $('#submitBtn').text('Submit');
                $('#attendanceModal').modal('show');
            });

            // edit
            $('body').on('click', '.edit_btn', function() {
                let id = datatable.row($(this).closest('tr')).data().id;

                $.ajax({
                    url: "{{ route('admin.attendance_types.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#modalTitle').text('Update Attendance Type');
                        $('#attendanceId').val(data.id);
                        $('[name="name"]').val(data.name);
                        $('[name="status"]').val(data.status);

                        // show status dropdown only in edit
                        $('#status_div').removeClass('d-none');

                        // change form action & method
                        $('#attendanceForm').attr('action', "{{ route('admin.attendance_types.update',':id') }}".replace(':id', id));
                        if ($('#attendanceForm input[name="_method"]').length === 0) {
                            $('#attendanceForm').append('<input type="hidden" name="_method" value="PUT">');
                        } else {
                            $('#attendanceForm input[name="_method"]').val('PUT');
                        }

                        $('#submitBtn').text('Update');
                        $('#attendanceModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Record not found!',
                        });
                    }
                });
            });

            // form validation
            $("#attendanceForm").on("submit", function (e) {
                let form = this;

                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                } else {
                    form.submit();
                }
                $(form).addClass("was-validated");
            });

        });
    </script>
@endpush
