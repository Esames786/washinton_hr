@extends('layout.master')
@section('pageName', 'Designations')

@section('content')
    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold">Designations</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#designationModal">+ Add Designation</button>
            </div>

            <table class="table table-bordered" id="designationTable">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="designationModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" id="designationForm" action="{{ route('admin.designations.store') }}">
                @csrf
                <input type="hidden" name="id" id="designation_id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Designation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Designation Name</label>
                            <input type="text" name="name" id="designation_name" class="form-control" required>
                        </div>
                        <div class="mb-3 d-none" id="status_wrapper">
                            <label class="form-label">Status</label>
                            <select name="status" id="designation_status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            let table = $('#designationTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.designations.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            $('body').on('click', '.edit_btn', function () {
                let id = table.row($(this).closest('tr')).data().id;

                $.ajax({
                    url: "{{ route('admin.designations.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function (data) {
                        // Modal Title
                        $('#modalTitle').text('Update Designation');

                        // Form fields fill karo
                        $('#designation_id').val(data.id);
                        $('[name="name"]').val(data.name);
                        $('[name="status"]').val(data.status);

                        // Form action change
                        $('#designationForm').attr(
                            'action',
                            "{{ route('admin.designations.update', ':id') }}".replace(':id', id)
                        );

                        // Add hidden _method input for PUT
                        if ($('#designationForm input[name="_method"]').length === 0) {
                            $('#designationForm').append('<input type="hidden" name="_method" value="PUT">');
                        } else {
                            $('#designationForm input[name="_method"]').val('PUT');
                        }

                        // Button text change
                        $('#submitBtn').text('Update');

                        // Modal show karo
                        $('#designationModal').modal('show');
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Record not found!',
                        });
                    }
                });
            });


            // delete button
            $(document).on('click', '.delete_btn', function() {
                if(confirm("Are you sure to delete this designation?")) {
                    let id = $(this).data('id');
                    $.ajax({
                        url: "{{ url('admin/designations') }}/" + id,
                        type: 'DELETE',
                        data: {_token: "{{ csrf_token() }}"},
                        success: function(res) {
                            table.ajax.reload();
                        }
                    });
                }
            });
        });
    </script>
@endpush
