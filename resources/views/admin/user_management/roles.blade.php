@extends('layout.master')

@section('pageName','Roles')

@push('cssLinks')
    <style>
        .table-text-center, th { text-align: center!important; }
        .dt-input{ padding:10px!important; }
        .dt-length label { margin-left: 10px!important; }
    </style>
@endpush

@section('content')
    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Roles</h5>
            <button class="btn btn-primary" id="addNewBtn">Add Role</button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-text-center" id="rolesTable" style="width:100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>User Type</th>
                    <th>Status</th>
                    <th>Access</th>
                    <th>Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Role Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog">
            <form  action="{{ route('admin.roles.store') }}" method="post" id="roleForm" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" id="role_id" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" id="role_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>User Type</label>
                            <select name="user_type" id="user_type" class="form-control" required>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary cancel_btn" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            // Form Validation
            $("#roleForm").on("submit", function (e) {
                let form = this;
                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                }
            });

            // Init DataTable
            let datatable = $('#rolesTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.roles.index') }}",
                    data: function (d) {
                        if (d.search && d.search.value) {
                            d.columns[1].search.value = d.search.value;
                            d.search.value = '';
                        }
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name', orderable: false },
                    { data: 'guard_name', name: 'guard_name', orderable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'access', name: 'access', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Add New
            $('#addNewBtn').click(function(){
                $('#roleForm')[0].reset();
                $('#roleForm').removeClass('was-validated');
                $('#roleId').val('');
                $('#modalTitle').text('Add Role');
                $('#roleForm').attr('action', "{{ route('admin.roles.store') }}");
                $('#roleForm input[name="_method"]').remove();
                $('#submitBtn').text('Save');
                $('[name="user_type"]').prop('disabled', false).prop('required', true);
                $('#roleModal').modal('show');
            });

            // Edit
            $('body').on('click', '.edit_btn', function() {
                let id = datatable.row($(this).closest('tr')).data().id;
                $.ajax({
                    url: "{{ route('admin.roles.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#modalTitle').text('Update Role');
                        $('#roleId').val(data.id);
                        $('[name="name"]').val(data.name);
                        $('[name="user_type"]').val(data.guard_name);
                        $('[name="status"]').val(data.status);

                        $('[name="user_type"]').prop('disabled', true).prop('required', false);
                        $('#roleForm').attr('action', "{{ route('admin.roles.update', ':id') }}".replace(':id', id));
                        if ($('#roleForm input[name="_method"]').length === 0) {
                            $('#roleForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        $('#submitBtn').text('Update');
                        $('#roleModal').modal('show');
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
                $('#roleForm')[0].reset();
                $('#submitBtn').text('Save');
                $('#modalTitle').text('Add Role');
                $('#roleForm').attr('action', "{{ route('admin.roles.store') }}");
                $('#roleForm').attr('method', 'POST');
                $('#roleForm input[name="_method"]').remove();
            });

        });
    </script>

@endpush
