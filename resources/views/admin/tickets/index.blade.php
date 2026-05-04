@extends('layout.master')

@section('pageName', 'Tickets')

@section('content')
    @include('partials.alerts')

{{--    <div class="card h-100 p-0 radius-12">--}}
{{--        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">--}}
{{--            <h5 class="mb-0">Tickets</h5>--}}
{{--            <button class="btn btn-primary btn-sm float-end" id="add_btn">--}}
{{--                <i class="bi bi-plus"></i> Create Ticket--}}
{{--            </button>--}}
{{--        </div>--}}
{{--        <div class="card-body p-24">--}}
{{--            <div class="table-responsive">--}}
{{--                <table class="table bordered-table sm-table mb-0 table-text-center" id="ticketTable">--}}
{{--                    <thead>--}}
{{--                    <tr>--}}

{{--                    </tr>--}}
{{--                    </thead>--}}
{{--                </table>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

    <div class="card h-100 p-0 radius-12">
{{--        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">--}}
{{--            <h5 class="mb-0">Tickets</h5>--}}
{{--            <div class="d-flex gap-2">--}}
{{--                <a href="{{route('admin.hr_employees.create')}}" class="btn btn-primary btn-sm">Add Employee</a>--}}
{{--            </div>--}}

{{--        </div>--}}
        <div class="card-body p-24">
            <div class="table-responsive">
                <table class="table bordered-table sm-table mb-0 table-text-center" id="ticketTable">
                    <thead>
                    <tr>
                        <th>Ticket#</th>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Subject</th>
                        <th>Description</th>
                        <th>Approved By</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal --}}
{{--    <div class="modal fade" id="ticketModal" tabindex="-1">--}}
{{--        <div class="modal-dialog modal-lg modal-dialog-centered">--}}
{{--            <div class="modal-content radius-16 bg-base">--}}
{{--                <div class="modal-header">--}}
{{--                    <h5 class="modal-title" id="ticketModalTitle">Create Ticket</h5>--}}
{{--                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>--}}
{{--                </div>--}}
{{--                <div class="modal-body">--}}
{{--                    <form method="post" id="ticketForm" class="needs-validation" novalidate>--}}
{{--                        @csrf--}}
{{--                        <input type="hidden" name="id" id="ticketId">--}}

{{--                        <div class="row pb-8">--}}
{{--                            <div class="col-6">--}}
{{--                                <label>Employee</label>--}}
{{--                                <select name="employee_id" id="employee_id" class="form-select" required>--}}
{{--                                    <option value="">Select Employee</option>--}}
{{--                                    @foreach($employees as $emp)--}}
{{--                                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>--}}
{{--                                    @endforeach--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                            <div class="col-6">--}}
{{--                                <label>Ticket Type</label>--}}
{{--                                <select name="ticket_type_id" id="ticket_type_id" class="form-select" required>--}}
{{--                                    <option value="">Select Type</option>--}}
{{--                                    @foreach($ticketTypes as $type)--}}
{{--                                        <option value="{{ $type->id }}" data-fields='@json($type->form_fields)'>{{ $type->name }}</option>--}}
{{--                                    @endforeach--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        <div id="dynamic-fields"></div>--}}

{{--                        <div class="text-end">--}}
{{--                            <button type="submit" id="ticketSubmitBtn" class="btn btn-primary">Save</button>--}}
{{--                            <button type="button" class="btn btn-secondary cancel_btn" data-bs-dismiss="modal">Cancel</button>--}}
{{--                        </div>--}}
{{--                    </form>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
@endsection

@push('scripts')
    <script>
        $(function(){
            let datatable = $('#ticketTable').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                rowId: 'id',
                ajax: "{{ route('admin.tickets.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'employee_name', name: 'employee_name' },
                    { data: 'ticket_type', name: 'ticket_type' },
                    { data: 'subject', name: 'subject' },
                    { data: 'description', name: 'description' },
                    { data: 'approved_by', name: 'approved_by' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            });

            // Approve Ticket
                $('body').on('click', '.approved_btn', function(){
                    let id = $(this).closest('tr').attr('id'); // ticket id from rowId

                    $.ajax({
                        url: "{{ route('admin.tickets.approve', ':id') }}".replace(':id', id),
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if(res.status){
                                Swal.fire('Approved!', res.message, 'success');
                                $('#ticketTable').DataTable().ajax.reload();
                            } else {
                                Swal.fire('Error!', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            if(xhr.status === 409){
                                // Custom leave check error
                                let message = xhr.responseJSON.message || 'Your request could not be processed due to a conflict.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: message
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Something went wrong. Please try again.'
                                });
                            }
                        }
                    });
                });

            $('body').on('click', '.reject_btn', function(){
                let id = $(this).closest('tr').attr('id');

                Swal.fire({
                    title: 'Reject Ticket',
                    input: 'textarea',
                    inputLabel: 'Remarks',
                    inputPlaceholder: 'Enter rejection reason...',
                    inputAttributes: {
                        'aria-label': 'Enter rejection reason'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    cancelButtonText: 'Cancel',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Remarks are required!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.tickets.reject', ':id') }}".replace(':id', id), // clean route replacement
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                remarks: result.value
                            },
                            success: function(res){
                                if(res.status){ // controller sends 'status' true/false
                                    Swal.fire('Rejected!', res.message, 'success');
                                    $('#ticketTable').DataTable().ajax.reload();
                                } else {
                                    Swal.fire('Error!', res.message || 'Something went wrong.', 'error');
                                }
                            },
                            error: function(xhr){
                                let message = 'Something went wrong.';
                                if(xhr.responseJSON && xhr.responseJSON.message){
                                    message = xhr.responseJSON.message;
                                }
                                Swal.fire('Error!', message, 'error');
                            }
                        });
                    }
                });
            });


            // Reject Ticket with Remarks
            {{--$('body').on('click', '.reject_btn', function(){--}}
            {{--    let id = $(this).closest('tr').attr('id');--}}

            {{--    Swal.fire({--}}
            {{--        title: 'Reject Ticket',--}}
            {{--        input: 'textarea',--}}
            {{--        inputLabel: 'Remarks',--}}
            {{--        inputPlaceholder: 'Enter rejection reason...',--}}
            {{--        inputAttributes: {--}}
            {{--            'aria-label': 'Enter rejection reason'--}}
            {{--        },--}}
            {{--        showCancelButton: true,--}}
            {{--        confirmButtonText: 'Reject',--}}
            {{--        cancelButtonText: 'Cancel',--}}
            {{--        inputValidator: (value) => {--}}
            {{--            if (!value) {--}}
            {{--                return 'Remarks are required!';--}}
            {{--            }--}}
            {{--        }--}}
            {{--    }).then((result) => {--}}
            {{--        if (result.isConfirmed) {--}}
            {{--            $.post("{{ url('admin/tickets') }}/" + id + "/reject", {--}}
            {{--                _token: "{{ csrf_token() }}",--}}
            {{--                remarks: result.value--}}
            {{--            }, function(res){--}}
            {{--                if(res.success){--}}
            {{--                    Swal.fire('Rejected!', res.message, 'success');--}}
            {{--                    $('#ticketTable').DataTable().ajax.reload();--}}
            {{--                } else {--}}
            {{--                    Swal.fire('Error!', res.message, 'error');--}}
            {{--                }--}}
            {{--            }).fail(function(){--}}
            {{--                Swal.fire('Error!', 'Something went wrong.', 'error');--}}
            {{--            });--}}
            {{--        }--}}
            {{--    });--}}
            {{--});--}}
            // Open Create Modal
            {{--$('#add_btn').click(function(){--}}
            {{--    $('#ticketForm')[0].reset();--}}
            {{--    $('#dynamic-fields').empty();--}}
            {{--    $('#ticketModalTitle').text('Create Ticket');--}}
            {{--    $('#ticketForm').attr('action', "{{ route('admin.tickets.store') }}");--}}
            {{--    $('#ticketModal').modal('show');--}}
            {{--});--}}

            // Dynamic fields based on ticket type
            {{--$('#ticket_type_id').on('change', function(){--}}
            {{--    let fields = $(this).find(':selected').data('fields');--}}
            {{--    let container = $('#dynamic-fields');--}}
            {{--    container.empty();--}}

            {{--    if (fields && fields.length > 0) {--}}
            {{--        fields.forEach(field => {--}}
            {{--            let html = '';--}}
            {{--            if (field.type === 'textarea') {--}}
            {{--                html = `<div class="mb-3">--}}
            {{--                    <label>${field.name}</label>--}}
            {{--                    <textarea name="fields[${field.name}]" class="form-control" ${field.required ? 'required' : ''}></textarea>--}}
            {{--                </div>`;--}}
            {{--            } else {--}}
            {{--                html = `<div class="mb-3">--}}
            {{--                    <label>${field.name}</label>--}}
            {{--                    <input type="${field.type}" name="fields[${field.name}]" class="form-control" ${field.required ? 'required' : ''}>--}}
            {{--                </div>`;--}}
            {{--            }--}}
            {{--            container.append(html);--}}
            {{--        });--}}
            {{--    }--}}
            {{--});--}}

            {{--// Change status buttons--}}
            {{--$('body').on('click', '.change-status', function(){--}}
            {{--    let id = $(this).data('id');--}}
            {{--    let status = $(this).data('status');--}}
            {{--    $.post("{{ url('admin/tickets') }}/" + id + "/status", {--}}
            {{--        _token: "{{ csrf_token() }}",--}}
            {{--        status_id: status--}}
            {{--    }, function(res){--}}
            {{--        datatable.ajax.reload();--}}
            {{--    });--}}
            {{--});--}}
        });
    </script>
@endpush
