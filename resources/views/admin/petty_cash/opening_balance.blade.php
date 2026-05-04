@extends('layout.master')

@section('pageName','Petty Cash Masters')

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
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Petty Cash Masters</h5>
            <div class="d-flex gap-2">
                @if(!$hasMaster)
                    <button class="btn btn-primary btn-sm" id="addMasterBtn">
                        Add Master
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="mastersTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Opening Balance</th>
                        <th>Current Balance</th>
{{--                        <th class="text-center">Action</th>--}}
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="masterModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Master</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="masterForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="masterId">
                        <div class="row pb-8">
                            <div class="col-12">
                                <label>Title</label>
                                <input type="text" name="title" id="masterTitle" class="form-control" required>
                            </div>
                        </div>
                        <div class="row pb-8 opening_balance_group">
                            <div class="col-12">
                                <label>Opening Balance</label>
                                <input type="number" name="opening_balance" id="masterOpening" class="form-control" step="0.01" required>
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
            let table = $('#mastersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.petty_cash.masters.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'title' },
                    { data: 'opening_balance', name: 'opening_balance' },
                    { data: 'current_balance', name: 'current_balance' },
                    // { data: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Add Master
            $('#addMasterBtn').click(function () {
                $('#masterForm')[0].reset();
                $("#masterForm").removeClass("was-validated");
                $('#masterId').val('');
                $('.opening_balance_group').show();
                $('#modalTitle').text('Add Master');
                $('#submitBtn').text('Save');
                $('#masterModal').modal('show');
            });

            // Save / Update
            $('#masterForm').submit(function (e) {
                e.preventDefault();
                let id = $('#masterId').val();
                let url = id ? "/admin/petty_cash/masters/" + id : "{{ route('admin.petty_cash.masters.store') }}";
                let method = id ? "PUT" : "POST";

                $.ajax({
                    url, method,
                    data: $(this).serialize(),
                    success: function (res) {
                        $('#masterModal').modal('hide');
                        table.ajax.reload();
                        toastr.success(res.message);
                    },
                    error: function () {
                        toastr.error("Something went wrong!");
                    }
                });
            });

            // Edit
            $(document).on('click', '.edit_btn', function () {
                let id = $(this).data('id');
                $.get("/admin/petty_cash/masters/" + id + "/edit", function (data) {
                    $("#masterForm").removeClass("was-validated");
                    $('#modalTitle').text('Update Master');
                    $('#masterId').val(data.id);
                    $('#masterTitle').val(data.title);
                    $('.opening_balance_group').hide();
                    $('#submitBtn').text('Update');
                    $('#masterModal').modal('show');
                });
            });

            // Delete with SweetAlert
            $(document).on('click', '.delete_btn', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This record will be deleted permanently!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/admin/petty_cash/masters/" + id,
                            method: "DELETE",
                            data: {_token: "{{ csrf_token() }}"},
                            success: function (res) {
                                table.ajax.reload();
                                Swal.fire('Deleted!', res.message, 'success');
                            },
                            error: function () {
                                Swal.fire('Error!', 'Delete failed.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
