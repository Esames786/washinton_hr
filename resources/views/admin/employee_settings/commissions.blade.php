@extends('layout.master')

@section('pageName','Commission Settings')
@push('cssLinks')
    <style>
        .table-text-center, th { text-align: center!important; }
        .dt-input { padding:10px!important; }
        .dt-length label { margin-left: 10px!important; }
        #slabTable th, #slabTable td { text-align: left!important; vertical-align: middle; }
        #slabTable th { white-space: nowrap; }
    </style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Commission Settings</h5>
            <button class="btn btn-primary btn-sm" id="addNewBtn">Add Commission Setting</button>
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

                        {{-- Row 1: Title | Commission Type | Slab Based toggle --}}
                        <div class="row pb-8">
                            <div class="col-4">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-4">
                                <label>Commission Type</label>
                                <select name="commission_type_id" class="form-select" required>
                                    <option value="">Select</option>
                                    @foreach($commissionTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4 d-flex flex-column justify-content-end pb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_slab_based" id="isSlabBased" value="1">
                                    <label class="form-check-label fw-semibold" for="isSlabBased">Slab Based</label>
                                    <small class="d-block text-neutral-500">Enable to define commission by profit brackets</small>
                                </div>
                            </div>
                        </div>

                        {{-- Row 2: Value (hidden when slab) | Target Type | Status --}}
                        <div class="row pb-8">
                            <div class="col-4" id="valueWrapper">
                                <label>Value</label>
                                <input type="number" step="0.01" name="value" class="form-control" id="valueInput">
                            </div>
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

                        {{-- Slab Editor (shown when Slab Based is checked) --}}
                        <div class="row pb-8" id="slabEditorWrapper" style="display:none;">
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="mb-0 fw-semibold">Commission Slabs</label>
                                    <button type="button" class="btn btn-success btn-sm" id="addSlabRowBtn">
                                        <iconify-icon icon="lucide:plus" class="me-1"></iconify-icon> Add Row
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm bordered-table" id="slabTable">
                                        <thead class="bg-neutral-100">
                                        <tr>
                                            <th>Profit From (USD)</th>
                                            <th>Profit To (USD)</th>
                                            <th>Value (% or PKR)</th>
                                            <th style="width:60px;"></th>
                                        </tr>
                                        </thead>
                                        <tbody id="slabRows"></tbody>
                                    </table>
                                </div>
                                <small class="text-neutral-500">Leave "Profit To" blank to make the last tier open-ended (no upper limit).</small>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="row pb-8">
                            <div class="col-12">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
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
        $(document).ready(function () {

            let slabRowIndex = 0;

            // ---------------------------------------------------------
            // Slab row helpers
            // ---------------------------------------------------------
            function buildSlabRow(profitFrom, profitTo, value) {
                let idx = slabRowIndex++;
                return `<tr>
                    <td><input type="number" step="0.01" min="0" name="slabs[${idx}][profit_from]"
                               class="form-control form-control-sm" value="${profitFrom ?? ''}" placeholder="e.g. 100" required></td>
                    <td><input type="number" step="0.01" min="0" name="slabs[${idx}][profit_to]"
                               class="form-control form-control-sm" value="${profitTo ?? ''}" placeholder="e.g. 500 (blank = unlimited)"></td>
                    <td><input type="number" step="0.01" min="0" name="slabs[${idx}][value]"
                               class="form-control form-control-sm" value="${value ?? ''}" placeholder="e.g. 10" required></td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm removeSlabRow px-8 py-4">
                            <iconify-icon icon="lucide:trash-2"></iconify-icon>
                        </button>
                    </td>
                </tr>`;
            }

            function toggleSlabMode(isSlab) {
                if (isSlab) {
                    $('#valueWrapper').hide();
                    $('#valueInput').removeAttr('required').val('');
                    $('#slabEditorWrapper').show();
                } else {
                    $('#slabEditorWrapper').hide();
                    $('#slabRows').empty();
                    $('#valueWrapper').show();
                    $('#valueInput').attr('required', 'required');
                }
            }

            function resetForm() {
                $('#commissionForm')[0].reset();
                $('#commissionForm').removeClass('was-validated');
                $('#settingId').val('');
                $('#isSlabBased').prop('checked', false);
                toggleSlabMode(false);
                slabRowIndex = 0;
            }

            // ---------------------------------------------------------
            // Slab checkbox toggle
            // ---------------------------------------------------------
            $('#isSlabBased').on('change', function () {
                toggleSlabMode(this.checked);
            });

            // ---------------------------------------------------------
            // Add slab row button
            // ---------------------------------------------------------
            $('#addSlabRowBtn').on('click', function () {
                $('#slabRows').append(buildSlabRow('', '', ''));
            });

            // ---------------------------------------------------------
            // Remove slab row (delegated)
            // ---------------------------------------------------------
            $('#slabRows').on('click', '.removeSlabRow', function () {
                $(this).closest('tr').remove();
            });

            // ---------------------------------------------------------
            // Form validation
            // ---------------------------------------------------------
            $('#commissionForm').on('submit', function (e) {
                let form = this;
                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass('was-validated');
                    return false;
                }
            });

            // ---------------------------------------------------------
            // DataTable
            // ---------------------------------------------------------
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
                    { data: 'title', name: 'title', orderable: false },
                    { data: 'description', name: 'description', orderable: false },
                    { data: 'commission_type', name: 'commission_type', orderable: false },
                    { data: 'value_display', name: 'value', orderable: false },
                    { data: 'target_type', name: 'target_type', orderable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            });

            // ---------------------------------------------------------
            // Add New
            // ---------------------------------------------------------
            $('#addNewBtn').on('click', function () {
                resetForm();
                $('#modalTitle').text('Add Commission Setting');
                $('#commissionForm').attr('action', "{{ route('admin.commission_settings.store') }}");
                $('#commissionForm input[name="_method"]').remove();
                $('#submitBtn').text('Save');
                $('#commissionModal').modal('show');
            });

            // ---------------------------------------------------------
            // Edit
            // ---------------------------------------------------------
            $('body').on('click', '.edit_btn', function () {
                let id = datatable.row($(this).closest('tr')).data().id;
                $.ajax({
                    url: "{{ route('admin.commission_settings.edit', ':id') }}".replace(':id', id),
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        resetForm();
                        $('#modalTitle').text('Update Commission Setting');
                        $('#settingId').val(data.id);
                        $('[name="title"]').val(data.title);
                        $('[name="commission_type_id"]').val(data.commission_type_id);
                        $('[name="target_type_id"]').val(data.target_type_id);
                        $('[name="status"]').val(data.status);
                        $('[name="description"]').val(data.description);

                        if (data.is_slab_based) {
                            $('#isSlabBased').prop('checked', true);
                            toggleSlabMode(true);
                            if (data.slabs && data.slabs.length > 0) {
                                data.slabs.forEach(function (slab) {
                                    $('#slabRows').append(buildSlabRow(slab.profit_from, slab.profit_to, slab.value));
                                });
                            }
                        } else {
                            $('[name="value"]').val(data.value);
                        }

                        $('#commissionForm').attr('action', "{{ route('admin.commission_settings.update', ':id') }}".replace(':id', id));
                        if ($('#commissionForm input[name="_method"]').length === 0) {
                            $('#commissionForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        $('#submitBtn').text('Update');
                        $('#commissionModal').modal('show');
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Record not found!' });
                    }
                });
            });

            // ---------------------------------------------------------
            // Cancel
            // ---------------------------------------------------------
            $('.cancel_btn').on('click', function () {
                resetForm();
                $('#submitBtn').text('Save');
                $('#modalTitle').text('Add Commission Setting');
                $('#commissionForm').attr('action', "{{ route('admin.commission_settings.store') }}");
                $('#commissionForm input[name="_method"]').remove();
            });

        });
    </script>
@endpush
