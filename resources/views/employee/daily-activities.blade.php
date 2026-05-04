@extends('layout.master')

@section('pageName', 'Employee Daily Activities')

@push('cssLinks')
    <style>
        /*.table-text-center, th { text-align: center!important; }*/
        .dt-input { padding:10px!important; }
        .dt-length label { margin-left: 10px!important; }
        .view-form-file{
            margin-top: 10px;
            margin-left: 10px;
        }
    </style>
@endpush

@section('content')

    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header">
            <h4 class="card-title">Employee Daily Activities</h4>
            <button class="btn btn-primary btn-sm float-end" id="add_btn">
                <i class="bi bi-plus"></i> Add New
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="activitiesTable" style="width: 100%;">
                <thead>
                <tr>
{{--                    <th>#</th>--}}
                    <th>Date</th>
                    <th>Field</th>
                    <th>Value</th>
                    <th class="text-center">Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="activityModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="activityModalTitle">Add Daily Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{route('employee.activities.store')}}" method="post" id="activityForm" class="needs-validation" novalidate enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" id="activityId">

                        <div id="dynamicFields"></div>

                        <div class="text-end">
                            <button type="submit" id="activitySubmitBtn" class="btn btn-primary">Save</button>
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

            // Form Validation
            // $("#activityForm").on("submit", function (e) {
            //     let form = this;
            //     if (form.checkValidity() === false) {
            //         e.preventDefault();
            //         e.stopImmediatePropagation();
            //         $(form).addClass("was-validated");
            //         return false;
            //     }
            // });

            // DataTable
            let datatable = $('#activitiesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('employee.activities.index') }}",
                    type: "GET"
                },
                columns: [
                    { data: 'activity_date', name: 'activity_date', title: 'Activity Date' },
                    { data: 'field_name', name: 'field_name', title: 'Field Name' },
                    { data: 'field_value', name: 'field_value', title: 'Value', orderable: false, searchable: false },
                    { data: 'action', name: 'action', title: 'Action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                columnDefs: [
                    { targets: [2, 3], className: 'text-center' },
                    { targets: 0, className: 'text-nowrap' }
                ]
            });


            // Generate Dynamic Fields
            function generateFields(fields) {
                let html = '';
                {{--fields.forEach(field => {--}}
                {{--    html += `<div class="mb-3">--}}
                {{--        <label class="mb-2">${field.label}</label>`;--}}
                {{--    if (field.type === 'text') {--}}
                {{--        html += `<input type="text" name="field_${field.id}"--}}
                {{--            class="form-control"--}}
                {{--            value="${field.value ?? ''}"--}}
                {{--            ${field.required ? 'required' : ''}>`;--}}
                {{--    }--}}
                {{--    if (field.type === 'file') {--}}
                {{--        html += `<input type="file" name="field_${field.id}"--}}
                {{--            class="form-control"--}}
                {{--            ${field.required ? 'required' : ''}>`;--}}
                {{--        if (field.value) {--}}
                {{--            let baseUrl = "{{ asset('') }}"; // Laravel asset base--}}
                {{--            html += `<a href="${baseUrl}${field.value}" target="_blank" class="view-form-file">View File</a>`;--}}

                {{--        }--}}
                {{--    }--}}
                {{--    html += `</div>`;--}}
                {{--});--}}
                fields.forEach(field => {
                    html += `<div class="mb-3">
                        <label class="mb-2">${field.label}</label>`;

                        // Text, Number, Date -> same input
                        if (['text', 'number', 'date'].includes(field.type)) {
                            html += `<input type="${field.type}" name="field_${field.id}"
                            class="form-control"
                            value="${field.value ?? ''}"
                            ${field.required ? 'required' : ''}>`;
                        }

                        // Textarea
                        if (field.type === 'textarea') {
                            html += `<textarea name="field_${field.id}"
                                class="form-control"
                                ${field.required ? 'required' : ''}>${field.value ?? ''}</textarea>`;
                        }

                        // File
                        if (field.type === 'file') {
                            html += `<input type="file" name="field_${field.id}"
                            class="form-control"
                            ${field.required ? 'required' : ''}>`;
                            if (field.value) {
                                let baseUrl = "{{ asset('') }}";
                                html += `<a href="${baseUrl}${field.value}" target="_blank" class="view-form-file">View File</a>`;
                            }
                        }

                    html += `</div>`;
                });
                $('#dynamicFields').html(html);
            }

                // Add New
            $('#add_btn').click(function () {
                $.get("{{ route('employee.activities.create') }}")
                    .done(function (fields) {
                        if (fields && fields.length > 0) {
                            generateFields(fields);
                            $('#activityForm')[0].reset();
                            $('#activityId').val('');
                            $('#activityForm').attr('action', "{{ route('employee.activities.store') }}");
                            $('#activityForm input[name="_method"]').remove();
                            $('#activityModalTitle').text('Add Daily Activity');
                            $('#activitySubmitBtn').text('Save');
                            $('#activityModal').modal('show');
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'No Fields Available',
                                text: 'No activity fields are assigned to your role.',
                            });
                        }
                    })
                    .fail(function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Unable to load fields. Please try again.',
                        });
                    });
            });

            // Edit
            $('body').on('click', '.edit_btn', function () {
                let id = $(this).data('id');
                let editUrl = "{{ route('employee.activities.edit', ':id') }}".replace(':id', id);

                $.get(editUrl)
                    .done(function (data) {
                        if (data.fields && data.fields.length > 0) {
                            generateFields(data.fields);

                            $('#activityId').val(data.id);

                            // Update route with dynamic ID
                            let updateUrl = "{{ route('employee.activities.update', ':id') }}";
                            updateUrl = updateUrl.replace(':id', id);
                            $('#activityForm').attr('action', updateUrl);

                            // Add _method hidden input for PUT
                            if ($('#activityForm input[name="_method"]').length === 0) {
                                $('#activityForm').append('<input type="hidden" name="_method" value="PUT">');
                            }

                            $('#activityModalTitle').text('Update Daily Activity');
                            $('#activitySubmitBtn').text('Update');
                            $('#activityModal').modal('show');
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'No Fields Available',
                                text: 'No activity fields are assigned for this activity.',
                            });
                        }
                    })
                    .fail(function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Unable to fetch activity details. Please try again.',
                        });
                    });
            });


            // Submit Form
            // $('#activityForm').on('submit', function(e) {
            //     e.preventDefault();
            //     let formData = new FormData(this);
            //     let id = $('#activityId').val();
            //     let url = id ? `/activities/${id}` : `/activities`;
            //     if (id) formData.append('_method', 'PUT');
            //
            //     $.ajax({
            //         url: url,
            //         type: 'POST',
            //         data: formData,
            //         processData: false,
            //         contentType: false,
            //         success: function(res) {
            //             $('#activityModal').modal('hide');
            //             datatable.ajax.reload();
            //             Swal.fire('Success', res.message, 'success');
            //         },
            //         error: function(err) {
            //             Swal.fire('Error', 'Please check your input', 'error');
            //         }
            //     });
            // });

            // Cancel
            $('.cancel_btn').on('click', function () {
                $('#activityForm')[0].reset();
                $('#activityId').val('');
            });

        });
    </script>
@endpush
