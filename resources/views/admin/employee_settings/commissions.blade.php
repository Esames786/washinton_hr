@extends('layout.master')

@section('pageName','Commission Settings')
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
            <h5 class="mb-0">Commission Settings</h5>
            <button class="btn btn-primary btn-sm" id="addNewBtn">
                Add Commission Setting
            </button>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="commissionTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Commission</th>
                        <th>Value</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="commissionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Commission Setting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.commission_settings.store') }}" method="post" id="commissionForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="settingId">

                        <div class="row pb-8">
                            <div class="col-4">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>

                            <div class="col-4">
                                <label>Commission Type</label>
                                <select name="commission_type_id" class="form-select" required>
                                    <option value="">Select</option>
                                    {{-- Loop Commission Types --}}
                                    @foreach($commissionTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-4">
                                <label>Value</label>
                                <input type="number" step="0.01" name="value" class="form-control" required>
                            </div>
                        </div>

                        <div class="row pb-8">
                            <div class="col-4">
                                <label>Target Type</label>
                                <select name="target_type_id" class="form-select">
                                    <option value="">Select</option>
                                    @foreach($targetTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-4">
                                <label>Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

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
            $("#commissionForm").on("submit", function (e) {
                let form = this;
                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                }
            });

            // Init DataTable
            let datatable = $('#commissionTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'asc']],
                searching: true,
                rowId: 'id',
                ajax: {
                    url: "{{ route('admin.commission_settings.index') }}",
                    data: function (d) {
                        if (d.search && d.search.value) {
                            d.columns[1].search.value = d.search.value;
                            d.search.value = '';
                        }
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'title',orderable: false },
                    { data: 'description', name: 'description' ,orderable: false},
                    { data: 'commission_type', name: 'commission_type',orderable: false },
                    { data: 'value', name: 'value' ,orderable: false},
                    { data: 'target_type', name: 'target_type',orderable: false },
                    { data: 'status', name: 'status' ,orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Add New
            $('#addNewBtn').click(function(){
                $('#commissionForm')[0].reset();
                $('#commissionForm').removeClass('was-validated');
                $('#settingId').val('');
                $('#modalTitle').text('Add Commission Setting');
                $('#commissionForm').attr('action', "{{ route('admin.commission_settings.store') }}");
                $('#commissionForm input[name="_method"]').remove();
                $('#submitBtn').text('Save');
                $('#commissionModal').modal('show');
            });

            // Edit
            $('body').on('click', '.edit_btn', function() {
                let id = datatable.row($(this).closest('tr')).data().id;
                $.ajax({
                    url: "{{ route('admin.commission_settings.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#modalTitle').text('Update Commission Setting');
                        $('#settingId').val(data.id);
                        $('[name="title"]').val(data.title);
                        $('[name="commission_type_id"]').val(data.commission_type_id);
                        $('[name="value"]').val(data.value);
                        $('[name="target_type_id"]').val(data.target_type_id);
                        $('[name="status"]').val(data.status);
                        $('[name="description"]').val(data.description);

                        $('#commissionForm').attr('action', "{{ route('admin.commission_settings.update',':id') }}".replace(':id', id));
                        if ($('#commissionForm input[name="_method"]').length === 0) {
                            $('#commissionForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        $('#submitBtn').text('Update');
                        $('#commissionModal').modal('show');
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
                $('#commissionForm')[0].reset();
                $('#submitBtn').text('Save');
                $('#modalTitle').text('Add Commission Setting');
                $('#commissionForm').attr('action', "{{ route('admin.commission_settings.store') }}");
                $('#commissionForm').attr('method', 'POST');
                $('#commissionForm input[name="_method"]').remove();
            });

        });
    </script>
@endpush
