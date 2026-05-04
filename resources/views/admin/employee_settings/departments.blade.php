{{-- resources/views/admin/departments/index.blade.php --}}
@extends('layout.master')

@section('pageName','Departments')
@push('cssLinks')
    <style>
        .table-text-center, th { text-align: center!important; }
        .dt-input{ padding:10px!important; }
        .dt-length label { margin-left: 10px!important; }
    </style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Departments</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" id="addNewBtn">Add Department</button>
            </div>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="departmentTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    <div class="modal fade" id="departmentModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.departments.store')}}" method="post" id="departmentForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="departmentId">

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
        $(function() {
            let datatable = $('#departmentTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.departments.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'status', name: 'status', orderable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
            });

            // Add New
            $('#addNewBtn').click(function(){
                $('#departmentForm')[0].reset();
                $('#departmentId').val('');
                $('#modalTitle').text('Add Department');
                $('#departmentForm').attr('action', "{{ route('admin.departments.store') }}");
                $('#departmentForm input[name="_method"]').remove();
                $('#status_div').addClass('d-none');
                $('#submitBtn').text('Submit');
                $('#departmentModal').modal('show');
            });

            // Edit
            $('body').on('click', '.edit_btn', function() {
                let id = datatable.row($(this).closest('tr')).data().id;

                $.ajax({
                    url: "{{ route('admin.departments.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#modalTitle').text('Update Department');
                        $('#departmentId').val(data.id);
                        $('[name="name"]').val(data.name);
                        $('[name="status"]').val(data.status);

                        $('#status_div').removeClass('d-none');

                        $('#departmentForm').attr('action', "{{ route('admin.departments.update',':id') }}".replace(':id', id));
                        if ($('#departmentForm input[name="_method"]').length === 0) {
                            $('#departmentForm').append('<input type="hidden" name="_method" value="PUT">');
                        } else {
                            $('#departmentForm input[name="_method"]').val('PUT');
                        }

                        $('#submitBtn').text('Update');
                        $('#departmentModal').modal('show');
                    }
                });
            });

            // Form validation
            $("#departmentForm").on("submit", function (e) {
                let form = this;
                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                } else {
                    form.submit();
                }
            });
        });
    </script>
@endpush
