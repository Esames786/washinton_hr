@extends('layout.master')

@section('pageName','Tax Slabs')
@push('cssLinks')
    <style>
        .table-text-center, th {
            text-align: center!important;
        }
        .dt-input{
            padding:10px!important;
        }
        .dt-length  label {
            margin-left: 10px!important;
        }
    </style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Tax Slab Settings</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" id="addNewBtn">
                    Add Tax Slab
                </button>
            </div>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="taxSlabTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Min Income</th>
                        <th>Max Income</th>
                        <th>Rate</th>
                        <th>Type</th>
                        <th>Global Cap</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Add / Edit Modal --}}
    <div class="modal fade" id="taxSlabModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Tax Slab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.tax_slabs.store') }}" method="post" id="taxSlabForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="slabId">

                        <div class="row pb-8">
                            <div class="col-6">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label>Type</label>
                                <select name="type" id="type" class="form-control" required>
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed</option>
                                </select>
                            </div>
                        </div>

                        <div class="row pb-8">
                            <div class="col-6">
                                <label>Min Income</label>
                                <input type="number" name="min_income" min="0" step="0.01" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label>Max Income</label>
                                <input type="number" name="max_income" min="0" step="0.01" class="form-control">
                            </div>
                        </div>

                        <div class="row pb-8">
                            <div class="col-6">
                                <label>Rate</label>
                                <input type="number" name="rate" min="0" step="0.01" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label>Global Cap</label>
                                <input type="number" name="global_cap" min="0" step="0.01" class="form-control">
                            </div>
                        </div>

                        <div class="row pb-8">
                            <div class="col-12">
                                <label>Description</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="row pb-8">
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
        $(document).ready(function() {

            // Initialize DataTable
            let datatable = $('#taxSlabTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.tax_slabs.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'title' },
                    { data: 'min_income', name: 'min_income' },
                    { data: 'max_income', name: 'max_income' },
                    { data: 'rate', name: 'rate' },
                    { data: 'type', name: 'type' },
                    { data: 'global_cap', name: 'global_cap' },
                    { data: 'description', name: 'description', orderable: false },
                    { data: 'status', name: 'status', orderable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[0, 'asc']],
                rawColumns: ['status','action']
            });

            // Add New
            $('#addNewBtn').click(function(){
                $('#taxSlabForm')[0].reset();
                $("#taxSlabForm").removeClass("was-validated");
                $('#slabId').val('');
                $('#modalTitle').text('Add Tax Slab');
                $('#taxSlabForm').attr('action', "{{ route('admin.tax_slabs.store') }}");
                $('#taxSlabForm input[name="_method"]').remove();
                $('#submitBtn').text('Submit');
                $('#taxSlabModal').modal('show');
            });

            // Edit
            $('body').on('click', '.edit_btn', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ route('admin.tax_slabs.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $("#taxSlabForm").removeClass("was-validated");
                        $('#modalTitle').text('Update Tax Slab');
                        $('#slabId').val(data.id);
                        $('[name="title"]').val(data.title);
                        $('[name="min_income"]').val(data.min_income);
                        $('[name="max_income"]').val(data.max_income);
                        $('[name="rate"]').val(data.rate);
                        $('[name="type"]').val(data.type);
                        $('[name="global_cap"]').val(data.global_cap);
                        $('[name="description"]').val(data.description);
                        $('[name="status"]').val(data.status);

                        // Change form action & method
                        $('#taxSlabForm').attr('action', "{{ route('admin.tax_slabs.update',':id') }}".replace(':id', id));
                        if ($('#taxSlabForm input[name="_method"]').length === 0) {
                            $('#taxSlabForm').append('<input type="hidden" name="_method" value="PUT">');
                        } else {
                            $('#taxSlabForm input[name="_method"]').val('PUT');
                        }

                        $('#submitBtn').text('Update');
                        $('#taxSlabModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Record not found!',
                        });
                    }
                });
            });

            // Form validation
            $("#taxSlabForm").on("submit", function (e) {
                let form = this;
                if (form.checkValidity() === false) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $(form).addClass("was-validated");
                    return false;
                } else {
                    $(form).addClass("was-validated");
                }
            });

        });
    </script>
@endpush
