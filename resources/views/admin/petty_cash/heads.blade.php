@extends('layout.master')

@section('pageName','Petty Cash Heads')

@push('cssLinks')
    <style>
        .table-text-center, th {
            text-align: center!important;
        }
        .dt-input{
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
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-end">
{{--            <h5 class="mb-0">Petty Cash Heads</h5>--}}
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm " id="addHeadBtn">
                    Add Head
                </button>
            </div>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="headsTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Created At</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="headModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Head</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="headForm"  class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="headId">
                        <div class="row pb-8">
                            <div class="col-12">
                                <label>Name</label>
                                <input type="text" name="name" id="headName" class="form-control" required>
                            </div>
                        </div>
                        <div class="row pb-8">
                            <div class="col-12" id="typeDiv">
                                <label>Type</label>
                                <select name="type" id="headType" class="form-select" required>
                                    <option value="">Select</option>
                                    <option value="expense">Expense</option>
                                    <option value="income">Income</option>
                                </select>
                            </div>
                        </div>
                        <div class="ow pb-8">
                            <div class="col-12 d-none" id="statusDiv">
                                <label>Status</label>
                                <select name="status" id="headStatus" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
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
        $(function () {
            let table = $('#headsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.petty_cash.heads.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'type', name: 'type' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'status', name: 'status' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Add Head
            $('#addHeadBtn').click(function () {
                $('#headForm')[0].reset();
                $("#headForm").removeClass("was-validated");
                $('#headId').val('');
                $('#modalTitle').text('Add Head');
                $('#submitBtn').text('Save');
                $('#typeDiv').show();
                $('#statusDiv').addClass('d-none');
                $('#headModal').modal('show');
            });

            // Save / Update
            // Save / Update with validation
            $('#headForm').on("submit", function (e) {
                e.preventDefault();

                let form = this;

                // Frontend validation (HTML5)
                if (form.checkValidity() === false) {
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                }

                let id = $('#headId').val();
                let url = id ? "/admin/petty_cash/heads/" + id : "{{ route('admin.petty_cash.heads.store') }}";
                let method = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    method: method,
                    data: $(form).serialize(),
                    success: function (res) {
                        $('#headModal').modal('hide');
                        table.ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr) {
                        let message = "Something went wrong!";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message,
                        });
                    }
                });

                $(form).addClass("was-validated");
            });


            // Edit
            $(document).on('click', '.edit_btn', function () {
                let id = $(this).data('id');
                $.get("/admin/petty_cash/heads/" + id + "/edit", function (data) {
                    $("#headForm").removeClass("was-validated");
                    $('#modalTitle').text('Update Head');
                    $('#headId').val(data.id);
                    $('#headName').val(data.name);
                    $('#headType').val(data.type);
                    $('#submitBtn').text('Update');
                    $('#typeDiv').hide();  // hide type on edit
                    $('#statusDiv').removeClass('d-none'); // show status
                    $('#headStatus').val(data.status);
                    $('#headModal').modal('show');
                });
            });

            // Delete
            $(document).on('click', '.delete_btn', function () {
                if (!confirm('Are you sure?')) return;
                let id = $(this).data('id');
                $.ajax({
                    url: "/admin/petty_cash/heads/" + id,
                    method: "DELETE",
                    data: {_token: "{{ csrf_token() }}"},
                    success: function (res) {
                        table.ajax.reload();
                        toastr.success(res.message);
                    },
                    error: function () {
                        toastr.error("Delete failed!");
                    }
                });
            });
        });
    </script>
@endpush
