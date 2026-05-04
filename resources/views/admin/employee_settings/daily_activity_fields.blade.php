@extends('layout.master')

@section('pageName', 'Daily Activity Fields')

@push('cssLinks')
    <style>
        /*.table-text-center, th {*/
        /*    text-align: center!important;*/
        /*}*/
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
            <h4 class="card-title">Daily Activity Fields</h4>
            <button class="btn btn-primary btn-sm float-end" id="add_btn">
                <i class="bi bi-plus"></i> Add New
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="dailyActivityTable" style="width: 100%;">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Field Name</th>
                    <th>Field Type</th>
                    <th>Required</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="dailyActivityModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Daily Activity Field</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.daily_activity_fields.store') }}" method="post" id="dailyActivityForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="fieldId">

                        <div class="row pb-8">
                            <div class="col-6">
                                <label>Field Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>

                            <div class="col-6">
                                <label>Field Type</label>
                                <select name="field_type" id="field_type" class="form-select" required>
                                    <option value="">Select</option>
                                    <option value="text">Text</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
{{--                                    <option value="time">Time</option>--}}
{{--                                    <option value="select">Select</option>--}}
                                    <option value="file">File</option>
                                </select>
                            </div>
                        </div>

                        <div class="row pb-8">
{{--                            <div class="col-6">--}}
{{--                                <label>Options (for select type)</label>--}}
{{--                                <input type="text" name="options" id="options" class="form-control" placeholder="Comma separated values">--}}
{{--                            </div>--}}
                            <div class="col-6">
                                <label>Required</label>
                                <select name="is_required" id="is_required" class="form-select" required>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <div class="col-6">
                                <label>Status</label>
                                <select name="status" id="status" class="form-select" required>
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
    {{--    <script src="{{ asset('assets/vendor/datatable/js/jquery.dataTables.min.js') }}"></script>--}}
    {{--    <script src="{{ asset('assets/vendor/datatable/js/dataTables.bootstrap5.min.js') }}"></script>--}}

    <script>
        $(function() {

            // Form Validation
            $("#dailyActivityForm").on("submit", function (e) {
                let form = this;
                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                }
            });

            let datatable = $('#dailyActivityTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.daily_activity_fields.index') }}",
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'field_type', name: 'field_type' },
                    { data: 'is_required', name: 'is_required' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Add New
            $('#add_btn').click(function(){
                $('#dailyActivityForm')[0].reset();
                $('#dailyActivityForm').removeClass('was-validated');
                $('#fieldId').val('');
                $('#modalTitle').text('Add Daily Activity Field');
                $('#dailyActivityForm').attr('action', "{{ route('admin.daily_activity_fields.store') }}");
                $('#dailyActivityForm input[name="_method"]').remove();
                $('#submitBtn').text('Save');
                $('#dailyActivityModal').modal('show');
            });

            // Edit
            $('body').on('click', '.edit_btn', function() {
                let id = datatable.row($(this).closest('tr')).data().id;
                $.ajax({
                    url: "{{ route('admin.daily_activity_fields.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#modalTitle').text('Update Daily Activity Field');
                        $('#fieldId').val(data.id);
                        $('#name').val(data.name);
                        $('#field_type').val(data.field_type);
                        $('#options').val(data.options);
                        $('#is_required').val(data.is_required);
                        $('#status').val(data.status);

                        $('#dailyActivityForm').attr('action', "{{ route('admin.daily_activity_fields.update',':id') }}".replace(':id', id));
                        if ($('#dailyActivityForm input[name="_method"]').length === 0) {
                            $('#dailyActivityForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        $('#submitBtn').text('Update');
                        $('#dailyActivityModal').modal('show');
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
                $('#dailyActivityForm')[0].reset();
                $('#submitBtn').text('Save');
                $('#modalTitle').text('Add Daily Activity Field');
                $('#dailyActivityForm').attr('action', "{{ route('admin.daily_activity_fields.store') }}");
                $('#dailyActivityForm').attr('method', 'POST');
                $('#dailyActivityForm input[name="_method"]').remove();
            });

        });
    </script>
@endpush
