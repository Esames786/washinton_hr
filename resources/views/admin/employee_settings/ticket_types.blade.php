@extends('layout.master')

@section('pageName', 'Ticket Types')

@section('content')
    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header">
            <h4 class="card-title">Ticket Types</h4>
            <button class="btn btn-primary btn-sm float-end" id="add_btn">
                <i class="bi bi-plus"></i> Add Ticket Type
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="ticketTypeTable" style="width: 100%;">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
{{--                    <th>Form Fields</th>--}}
                    <th>Status</th>
{{--                    <th>Created At</th>--}}
                    <th class="text-center">Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="ticketTypeModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="ticketTypeModalTitle">Add Ticket Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="ticketTypeForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="id" id="ticketTypeId">

                        <div class="row pb-8">
                            <div class="col-12">
                                <label>Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
{{--                            <div class="col-12">--}}
{{--                                <label>Description</label>--}}
{{--                                <textarea name="description" id="description" class="form-control"></textarea>--}}
{{--                            </div>--}}
                            <div class="col-12" id="status_div">
                                <label>Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" id="ticketTypeSubmitBtn" class="btn btn-primary">Save</button>
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
            let formFields = [];

            function renderFields() {
                let container = $('#form-fields-container');
                container.empty();

                formFields.forEach((field, index) => {
                    container.append(`
                <div class="d-flex gap-2 mb-2 align-items-center">
                    <input type="text" class="form-control" placeholder="Field Name"
                        value="${field.name}" onchange="updateField(${index}, 'name', this.value)">
                    <select class="form-select" onchange="updateField(${index}, 'type', this.value)">
                        <option value="text" ${field.type === 'text' ? 'selected' : ''}>Text</option>
                        <option value="textarea" ${field.type === 'textarea' ? 'selected' : ''}>Textarea</option>
                        <option value="file" ${field.type === 'file' ? 'selected' : ''}>File</option>
                        <option value="date" ${field.type === 'date' ? 'selected' : ''}>Date</option>
                        <option value="time" ${field.type === 'time' ? 'selected' : ''}>Time</option>
                        <option value="number" ${field.type === 'number' ? 'selected' : ''}>Number</option>
                    </select>
                    <select class="form-select" onchange="updateField(${index}, 'required', this.value)">
                        <option value="1" ${field.required ? 'selected' : ''}>Required</option>
                        <option value="0" ${!field.required ? 'selected' : ''}>Optional</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeField(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `);
                });

                $('#form_fields').val(JSON.stringify(formFields));
            }

            window.updateField = function(index, key, value) {
                if (key === 'required') {
                    formFields[index][key] = value == "1";
                } else {
                    formFields[index][key] = value;
                }
                renderFields();
            };

            window.removeField = function(index) {
                formFields.splice(index, 1);
                renderFields();
            };

            $('#addFieldBtn').click(function () {
                formFields.push({ name: '', type: 'text', required: true });
                renderFields();
            });

            let datatable = $('#ticketTypeTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.ticket_types.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    // { data: 'form_fields', name: 'form_fields' },
                    { data: 'status', name: 'status' },
                    // { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Add
            $('#add_btn').click(function(){
                $('#ticketTypeForm')[0].reset();
                $("#ticketTypeForm").removeClass("was-validated");
                $('#ticketTypeId').val('');
                formFields = [];
                renderFields();
                $('#ticketTypeModalTitle').text('Add Ticket Type');
                $('#ticketTypeForm').attr('action', "{{ route('admin.ticket_types.store') }}");
                $('#ticketTypeForm input[name="_method"]').remove();
                $('#ticketTypeSubmitBtn').text('Save');
                $("#status_div").hide();
                $("#status").removeAttr('required')
                $('#ticketTypeModal').modal('show');
            });

            // Edit
            $('body').on('click', '.edit_btn', function() {
                let id = $(this).data('id');
                $.get("{{ url('admin/ticket_types') }}/" + id + "/edit", function(data) {
                    $('#ticketTypeId').val(data.id);
                    $('#name').val(data.name);
                    $('#description').val(data.description);
                    $('#status').val(data.status);
                    $("#request_type_id").val(data.request_type_id);
                    $("#status").attr('required',true)
                    $("#status_div").show();
                    formFields = data.form_fields || [];
                    renderFields();
                    $("#ticketTypeForm").removeClass("was-validated");
                    $('#ticketTypeModalTitle').text('Update Ticket Type');
                    $('#ticketTypeForm').attr('action', "{{ url('admin/ticket_types') }}/" + id);
                    if ($('#ticketTypeForm input[name="_method"]').length === 0) {
                        $('#ticketTypeForm').append('<input type="hidden" name="_method" value="PUT">');
                    }
                    $('#ticketTypeSubmitBtn').text('Update');
                    $('#ticketTypeModal').modal('show');
                });
            });

            $("#ticketTypeForm").on("submit", function (e) {
                let form = this;

                if (form.checkValidity() === false) {
                    e.preventDefault();      // stop the form
                    e.stopImmediatePropagation(); // stop any other submit handlers
                    $(form).addClass("was-validated");
                    return false; // extra safety
                }else {
                    form.submit();
                }
                // mark valid before native submission
                $(form).addClass("was-validated");
            });

        });
    </script>
@endpush
