@extends('layout.master')

@section('pageName','Employee Document Settings')
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
            <h5 class="mb-0">Employee Document Settings</h5>
            <button class="btn btn-primary btn-sm" id="addNewBtn">
                Add Document Setting
            </button>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="documentTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Is Required</th>
                        <th>Description</th>
                        <th>Input Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="documentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Document Setting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.document_settings.store') }}" method="post" id="documentForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="settingId">

                        <div class="row pb-8">
                            <div class="col-4">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>

                            <div class="col-4">
                                <label>Is Required</label>
                                <select name="is_required" class="form-select">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <div class="col-4">
                                <label>Input Type</label>
                                <select name="input_type" class="form-select">
                                    <option value="file">File</option>
                                    <option value="text">Text</option>
                                    <option value="date">Date</option>
                                </select>
                            </div>
                        </div>

{{--                        <div class="row pb-8">--}}
{{--                  --}}
{{--                            <div class="col-6">--}}
{{--                                <label>Status</label>--}}
{{--                                <select name="status" class="form-select">--}}
{{--                                    <option value="1">Active</option>--}}
{{--                                    <option value="0">Inactive</option>--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                        </div>--}}

                        <div class="row pb-8">
                            <div class="col-12">
                                <label>Description</label>
                                <textarea name="description" class="form-control"></textarea>
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

            // Form Validation
            $("#documentForm").on("submit", function (e) {
                let form = this;
                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                }
            });

            // Init DataTable
            let datatable = $('#documentTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.document_settings.index') }}",
                    data: function (d) {
                        if (d.search && d.search.value) {
                            d.columns[1].search.value = d.search.value;
                            d.search.value = '';
                        }
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'title' },
                    { data: 'is_required', name: 'is_required' },
                    { data: 'description', name: 'description' },
                    { data: 'input_type', name: 'input_type' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Add New
            $('#addNewBtn').click(function(){
                $('#documentForm')[0].reset();
                $('#documentForm').removeClass('was-validated');
                $('#settingId').val('');
                $('#modalTitle').text('Add Document Setting');
                $('#documentForm').attr('action', "{{ route('admin.document_settings.store') }}");
                $('#documentForm input[name="_method"]').remove();
                $('#submitBtn').text('Save');
                $('#documentModal').modal('show');
            });

            // Edit
            $('body').on('click', '.edit_btn', function() {
                let id = datatable.row($(this).closest('tr')).data().id;
                $.ajax({
                    url: "{{ route('admin.document_settings.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#modalTitle').text('Update Document Setting');
                        $('#settingId').val(data.id);
                        $('[name="title"]').val(data.title);
                        $('[name="is_required"]').val(data.is_required);
                        $('[name="input_type"]').val(data.input_type);
                        $('[name="status"]').val(data.status);
                        $('[name="description"]').val(data.description);

                        $('#documentForm').attr('action', "{{ route('admin.document_settings.update',':id') }}".replace(':id', id));
                        if ($('#documentForm input[name="_method"]').length === 0) {
                            $('#documentForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        $('#submitBtn').text('Update');
                        $('#documentModal').modal('show');
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
                $('#documentForm')[0].reset();
                $('#submitBtn').text('Save');
                $('#modalTitle').text('Add Document Setting');
                $('#documentForm').attr('action', "{{ route('admin.document_settings.store') }}");
                $('#documentForm').attr('method', 'POST');
                $('#documentForm input[name="_method"]').remove();
            });

        });
    </script>
@endpush
