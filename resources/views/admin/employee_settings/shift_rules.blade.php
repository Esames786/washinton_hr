@extends('layout.master')

@section('pageName', 'Shift Attendance Rule')

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

    <div class="card h-100">
        <div class="card-header">
            <h4 class="card-title">Shift Attendance Rule</h4>
{{--            <button class="btn btn-primary btn-sm float-end" id="add_btn">--}}
{{--                <i class="bi bi-plus"></i> Add New--}}
{{--            </button>--}}
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="shiftAttendanceTable" style="width: 100%;">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Shift Type</th>
                    <th>Attendance Status</th>
                    <th>Entry Time</th>
                    <th>Entry Weight</th>
                    <th>Status</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="shiftAttendanceModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="shiftModalTitle">Add Shift Attendance Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.shift_attendance_rules.store') }}" method="post" id="shiftAttendanceForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="shiftRuleId">

                        <div class="row pb-8">
                            <div class="col-4">
                                <label>Shift Type</label>
                                <select name="shift_type_id" id="shift_type_id" class="form-select" required>
                                    <option value="">Select</option>
                                    @foreach($shiftTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-4">
                                <label>Attendance Status</label>
                                <select name="attendance_status_id" id="attendance_status_id" class="form-select" required>
                                    <option value="">Select</option>
                                    @foreach($attendanceStatuses as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-4">
                                <label>Entry Time</label>
                                <input type="time" name="entry_time" id="entry_time" class="form-control" required>
                            </div>
                        </div>

                        <div class="row pb-8">
                            <div class="col-4">
                                <label>Entry Weight</label>
                                <input type="number" step="0.01" name="entry_weight" id="entry_weight" class="form-control" required>
                            </div>

                            <div class="col-4">
                                <label>Status</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" id="shiftSubmitBtn" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-secondary cancel_btn" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
{{--    <script src="{{ asset('assets/vendor/datatable/js/jquery.dataTables.min.js') }}"></script>--}}
{{--    <script src="{{ asset('assets/vendor/datatable/js/dataTables.bootstrap5.min.js') }}"></script>--}}

    <script>
        $(function() {

            // Form Validation
            $("#shiftAttendanceForm").on("submit", function (e) {
                let form = this;
                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                }
            });

            let datatable = $('#shiftAttendanceTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[1, 'asc'],[3, 'asc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.shift_attendance_rules.index') }}",
                    data: function (d) {
                        if (d.search && d.search.value) {
                            d.columns[1].search.value = d.search.value;
                            d.search.value = '';
                        }
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'shift_type_name', name: 'shift_type_name', orderable: false },
                    { data: 'attendance_status_name', name: 'attendance_status_name', orderable: false },
                    { data: 'entry_time', name: 'entry_time', orderable: false },
                    { data: 'entry_weight', name: 'entry_weight', orderable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Add New
            $('#add_btn').click(function(){
                $('#shiftAttendanceForm')[0].reset();
                $('#shiftAttendanceForm').removeClass('was-validated');
                $('#shiftRuleId').val('');
                $('#shiftModalTitle').text('Add Shift Attendance Rule');
                $('#shiftAttendanceForm').attr('action', "{{ route('admin.shift_attendance_rules.store') }}");
                $('#shiftAttendanceForm input[name="_method"]').remove();
                $('#shiftSubmitBtn').text('Save');
                $('#shiftAttendanceModal').modal('show');
            });

            // Edit
            $('body').on('click', '.edit_btn', function() {
                let id = datatable.row($(this).closest('tr')).data().id;
                $.ajax({
                    url: "{{ route('admin.shift_attendance_rules.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#shiftModalTitle').text('Update Shift Attendance Rule');
                        $('#shiftRuleId').val(data.id);
                        $('#shift_type_id').val(data.shift_type_id);
                        $('#attendance_status_id').val(data.attendance_status_id);
                        $('#entry_time').val(data.entry_time);
                        $('#entry_weight').val(data.entry_weight);
                        $('#status').val(data.status);

                        $('#shiftAttendanceForm').attr('action', "{{ route('admin.shift_attendance_rules.update',':id') }}".replace(':id', id));
                        if ($('#shiftAttendanceForm input[name="_method"]').length === 0) {
                            $('#shiftAttendanceForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        $('#shiftSubmitBtn').text('Update');
                        $('#shiftAttendanceModal').modal('show');
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Record not found!',
                        });
                    }
                });
            });

            // Cancel Button
            $('.cancel_btn').on('click', function () {
                $('#shiftAttendanceForm')[0].reset();
                $('#shiftSubmitBtn').text('Save');
                $('#shiftModalTitle').text('Add Shift Attendance Rule');
                $('#shiftAttendanceForm').attr('action', "{{ route('admin.shift_attendance_rules.store') }}");
                $('#shiftAttendanceForm').attr('method', 'POST');
                $('#shiftAttendanceForm input[name="_method"]').remove();
            });

        });
    </script>
@endpush
