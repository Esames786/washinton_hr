@extends('layout.master')
@section('pageName', 'Employee Tickets')

@section('content')
    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header">
{{--            <h4 class="card-title">Employee Tickets</h4>--}}
            <button class="btn btn-primary btn-sm float-end" id="add_btn">
                <i class="bi bi-plus"></i> New Ticket
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="ticketTable" style="width: 100%;">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th class="text-center">Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="ticketModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header">
                    <h5 class="modal-title" id="ticketModalTitle">New Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('employee.tickets.store') }}" method="post" id="ticketForm" class="needs-validation" novalidate enctype="multipart/form-data">
                        @csrf
                        <div class="row pb-8">
                            <div class="col-6">
                                <label>Ticket Type</label>
                                <select name="ticket_type_id" id="ticket_type_id" class="form-select" required>
                                    <option value="">Select</option>
                                    @foreach($ticketTypes as $type)
{{--                                        <option value="{{ $type->id }}" data-fields='@json($type->form_fields)'>{{ $type->name }}</option>--}}
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>

                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label>Subject</label>
                                <input type="text" name="subject" class="form-control" required>
                            </div>
                        </div>

                        <div class="row pb-8 dynamic-field attendance-fields d-none">
                            <div class="col-6">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control">
                            </div>
                            <div class="col-6">
                                <label>Check-In Time</label>
                                <input type="time" name="check_in" class="form-control">
                            </div>
                            <div class="col-6">
                                <label>Check-Out Time</label>
                                <input type="time" name="check_out" class="form-control">
                            </div>
                        </div>

                        <div class="row pb-8 dynamic-field leave-fields d-none">
                            <div class="col-6">
                                <label>From Date</label>
                                <input type="date" name="from_date" class="form-control">
                            </div>
                            <div class="col-6">
                                <label>To Date</label>
                                <input type="date" name="to_date" class="form-control">
                            </div>
                            <div class="col-6">
                                <label>Leave Type</label>
                                <select name="leave_type" class="form-select">
                                    <option value="">Select</option>
                                    <option value="annual">Annual Leave</option>
                                    <option value="sick">Sick Leave</option>
                                    <option value="casual">Casual Leave</option>
                                </select>
                            </div>
                        </div>

                        <div class="row pb-8">
                            <div class="col-12">
                                <label id="description_label">Description</label>
                                <textarea name="description" id="description" class="form-control" required></textarea>
                            </div>
                        </div>
{{--                        <div id="dynamic-fields" class="row"></div>--}}

                        <div class="text-end">
                            <button type="submit" id="ticketSubmitBtn" class="btn btn-primary">Save</button>
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

        $(function(){

            const ticketTypeSelect = document.getElementById('ticket_type_id');
            const description = document.getElementById('description');

            function resetFields() {
                // Hide all dynamic fields
                $('.attendance-fields, .leave-fields').addClass('d-none');
                // Remove required attributes
                $('.attendance-fields input, .leave-fields input, .leave-fields select').prop('required', false);
                // Reset description
                $("#description_label").text('Description')

            }

            ticketTypeSelect.addEventListener('change', function() {
                resetFields();
                const type = parseInt(this.value);

                if (type === 1) {
                    // Attendance Request
                    $('.attendance-fields').removeClass('d-none');
                    $('.attendance-fields input').prop('required', true);
                    $("#description_label").text('Remarks');
                } else if (type === 2) {
                    // Leave Request
                    $('.leave-fields').removeClass('d-none');
                    $('.leave-fields input, .leave-fields select').prop('required', true);
                    $("#description_label").text('Reason')
                }
            });

            // Reset modal when opening
            $('#add_btn').click(function(){
                $('#ticketForm')[0].reset();
                resetFields();
                $('#ticketModal').modal('show');
            });

            $("#ticketForm").on("submit", function (e) {
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

            let datatable = $('#ticketTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('employee.tickets.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'ticket_type', name: 'ticket_type' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });


            // $('#ticket_type_id').on('change', function(){
            //     let fields = $(this).find(':selected').data('fields');
            //     let container = $('#dynamic-fields');
            //     container.empty();
            //     if (fields && fields.length > 0) {
            //         fields.forEach(field => {
            //             let html = '';
            //             if (field.type === 'textarea') {
            //                 html = `<div class="mb-3 col-6">
            //                     <label>${field.name}</label>
            //                     <textarea name="fields[${field.slug}]" class="form-control" ${field.required ? 'required' : ''}></textarea>
            //                 </div>`;
            //             } else {
            //                 html = `<div class="mb-3 col-6">
            //                     <label>${field.name}</label>
            //                     <input type="${field.type}" name="fields[${field.slug}]" class="form-control" ${field.required ? 'required' : ''}>
            //                 </div>`;
            //             }
            //             container.append(html);
            //         });
            //     }
            // });
        });
    </script>
@endpush
