@extends('layout.master')
@section('pageName', 'Employee Tickets')

@section('content')
    @include('partials.alerts')

    <div class="card h-100">
        <div class="card-header">
            <button class="btn btn-primary btn-sm float-end" id="add_btn">
                <i class="bi bi-plus"></i> New Ticket
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="ticketTable" style="width: 100%;">
                <thead>
                <tr>
                    <th>Ticket#</th>
                    <th>Type</th>
                    <th>Employee</th>
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
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label>Subject</label>
                                <input type="text" name="subject" class="form-control" required>
                            </div>
                        </div>

                        {{-- Attendance Fields --}}
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

                        {{-- Leave Fields --}}
                        <div class="row pb-8 dynamic-field leave-fields d-none">
                            <div class="col-6">
                                <label>From Date</label>
                                <input type="date" name="from_date" class="form-control">
                            </div>
                            <div class="col-6">
                                <label>To Date</label>
                                <input type="date" name="to_date" class="form-control">
                            </div>
                            <div class="col-6 mt-2">
                                <label>Leave Type</label>
                                <select name="leave_type" class="form-select">
                                    <option value="">Select</option>
                                    @foreach($leave_types as $leave_type)
                                        <option value="{{$leave_type->id}}">{{$leave_type->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Description / Reason --}}
                        <div class="row pb-8">
                            <div class="col-12">
                                <label id="description_label">Description</label>
                                <textarea name="description" id="description" class="form-control" required></textarea>
                            </div>
                        </div>

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

            function resetFields() {
                $('.attendance-fields, .leave-fields').addClass('d-none');
                $('.attendance-fields input, .leave-fields input, .leave-fields select').prop('required', false);
                $("#description_label").text('Description');
                $('.invalid-feedback').remove(); // clear old errors
                $('#ticketForm').find('.is-invalid').removeClass('is-invalid');
            }

            ticketTypeSelect.addEventListener('change', function() {
                resetFields();
                const type = parseInt(this.value);

                if (type === 1) { // Attendance Request
                    $('.attendance-fields').removeClass('d-none');
                    $('.attendance-fields input').prop('required', true);
                    $("#description_label").text('Remarks');
                } else if (type === 2) { // Leave Request
                    $('.leave-fields').removeClass('d-none');
                    $('.leave-fields input, .leave-fields select').prop('required', true);;
                    $("#description_label").text('Reason');
                }
            });

            // Reset modal
            $('#add_btn').click(function(){
                $('#ticketForm')[0].reset();
                resetFields();
                $('#ticketModal').modal('show');
            });

            // AJAX form submission
            $("#ticketForm").on("submit", function(e){
                e.preventDefault();
                let form = this;
                $(form).addClass("was-validated");

                let formData = new FormData(form);

                $.ajax({
                    url: $(form).attr('action'),
                    method: $(form).attr('method'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res){
                        if(res.status){
                            $('#ticketModal').modal('hide');
                            $('#ticketForm')[0].reset();
                            resetFields();
                            $('#ticketTable').DataTable().ajax.reload();
                            Swal.fire('Success', res.message, 'success');
                        }
                    },
                    error: function(xhr){
                        $(form).find('.invalid-feedback').remove();
                        $(form).find('.is-invalid').removeClass('is-invalid');

                        if(xhr.status === 422){
                            let errors = xhr.responseJSON.errors;
                            let allErrors = [];
                            for(let field in errors){
                                // let input = $(form).find('[name="'+field+'"]');
                                // if(input.length){
                                //     input.addClass('is-invalid');
                                //     input.after('<div class="invalid-feedback">'+errors[field][0]+'</div>');
                                // }
                                allErrors.push(errors[field][0]);
                            }
                            // Show all validation errors in SweetAlert
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                html: allErrors.join('<br>')
                            });
                        }  else if(xhr.status === 409){
                            // Custom leave check error
                            let message = xhr.responseJSON.message || 'Your request could not be processed due to a conflict';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: message
                            });
                        }  else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong. Please try again.'
                            });
                        }
                    }
                });
            });

            // Initialize DataTable
            $('#ticketTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('employee.tickets.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'ticket_type', name: 'ticket_type' },
                    { data: 'employee_name', name: 'employee_name' }, // 👈 add if required
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false,className: "text-center" }
                ]
            });
        });
    </script>
@endpush
